from datetime import datetime
from flask import Flask, jsonify, request, render_template, send_file
from werkzeug.security import check_password_hash, generate_password_hash
from openpyxl import Workbook
from io import BytesIO

from config import Config
from db import get_db, close_db, init_db
from hardware import Buzzer
from gsm import send_sms
from utils import save_base64_image, mock_face_verify

app = Flask(__name__)
app.config.from_object(Config)
init_db(app)
buzzer = Buzzer(app.config['BUZZER_PIN'])


@app.teardown_appcontext
def teardown_db(exception=None):
    close_db(exception)


@app.route('/seed', methods=['POST'])
def seed_user():
    """Helper endpoint to create users quickly for demo/testing."""
    payload = request.get_json(force=True)
    name = payload.get('name', '').strip()
    phone = payload.get('phone', '').strip()
    password = payload.get('password', '').strip()
    if not (name and phone and password):
        return jsonify({'error': 'name, phone, password required'}), 400

    db = get_db(app)
    try:
        db.execute('INSERT INTO users(name, phone, password) VALUES (?, ?, ?)',
                   (name, phone, generate_password_hash(password)))
        db.commit()
        return jsonify({'message': 'User created'}), 201
    except Exception as exc:
        return jsonify({'error': str(exc)}), 400


@app.route('/login', methods=['POST'])
def login():
    payload = request.get_json(force=True)
    phone = payload.get('phone', '').strip()
    password = payload.get('password', '')

    if not phone or not password:
        return jsonify({'error': 'phone and password are required'}), 400

    db = get_db(app)
    user = db.execute('SELECT * FROM users WHERE phone = ?', (phone,)).fetchone()
    if not user or not check_password_hash(user['password'], password):
        return jsonify({'error': 'Invalid credentials'}), 403

    return jsonify({
        'message': 'Login successful',
        'employee_id': user['id'],
        'name': user['name']
    })


@app.route('/mark_api', methods=['POST'])
def mark_api():
    payload = request.get_json(force=True)
    employee_id = payload.get('employee_id')
    image_base64 = payload.get('image_base64', '')

    if not employee_id or not image_base64:
        buzzer.beep_error()
        return jsonify({'error': 'employee_id and image_base64 are required'}), 400

    db = get_db(app)
    user = db.execute('SELECT * FROM users WHERE id = ?', (employee_id,)).fetchone()
    if not user:
        buzzer.beep_error()
        return jsonify({'error': 'Employee not found'}), 404

    try:
        filename = save_base64_image(image_base64, employee_id, app.config['IMAGE_DIR'])
    except Exception:
        buzzer.beep_error()
        return jsonify({'error': 'Invalid image payload'}), 400

    if app.config['FACE_REQUIRED'] and not mock_face_verify(employee_id, filename):
        buzzer.beep_error()
        return jsonify({'error': 'Face mismatch'}), 403

    now = datetime.now()
    date_str = now.strftime('%Y-%m-%d')
    time_str = now.strftime('%H:%M:%S')

    row = db.execute('SELECT * FROM attendance WHERE employee_id = ? AND date = ?',
                     (employee_id, date_str)).fetchone()

    if row is None:
        db.execute('INSERT INTO attendance(employee_id, date, in_time, in_image) VALUES (?, ?, ?, ?)',
                   (employee_id, date_str, time_str, filename))
        db.commit()
        buzzer.beep_success()
        if app.config['ENABLE_GSM']:
            send_sms(app.config['GSM_PORT'], app.config['GSM_BAUD'], user['phone'],
                     f"Employee {user['name']} marked IN at {time_str}")
        return jsonify({'message': 'IN marked', 'status': 'IN', 'image_file': filename})

    if row['in_time'] and not row['out_time']:
        db.execute('UPDATE attendance SET out_time = ?, out_image = ? WHERE id = ?',
                   (time_str, filename, row['id']))
        db.commit()
        buzzer.beep_success()
        if app.config['ENABLE_GSM']:
            send_sms(app.config['GSM_PORT'], app.config['GSM_BAUD'], user['phone'],
                     f"Employee {user['name']} marked OUT at {time_str}")
        return jsonify({'message': 'OUT marked', 'status': 'OUT', 'image_file': filename})

    buzzer.beep_error()
    return jsonify({'error': 'Already marked IN and OUT today. Third scan rejected.'}), 403


@app.route('/attendance', methods=['GET'])
def attendance_api():
    db = get_db(app)
    rows = db.execute('''
        SELECT a.id, u.name, u.phone, a.employee_id, a.date, a.in_time, a.out_time, a.in_image, a.out_image
        FROM attendance a
        JOIN users u ON u.id = a.employee_id
        ORDER BY a.date DESC, a.id DESC
    ''').fetchall()
    return jsonify([dict(r) for r in rows])


@app.route('/')
def dashboard():
    db = get_db(app)
    rows = db.execute('''
        SELECT a.id, u.name, u.phone, a.employee_id, a.date, a.in_time, a.out_time, a.in_image, a.out_image
        FROM attendance a
        JOIN users u ON u.id = a.employee_id
        ORDER BY a.date DESC, a.id DESC
    ''').fetchall()
    return render_template('dashboard.html', rows=rows)


@app.route('/export')
def export_excel():
    db = get_db(app)
    rows = db.execute('''
        SELECT u.name, u.phone, a.employee_id, a.date, a.in_time, a.out_time, a.in_image, a.out_image
        FROM attendance a
        JOIN users u ON u.id = a.employee_id
        ORDER BY a.date DESC, a.id DESC
    ''').fetchall()

    wb = Workbook()
    ws = wb.active
    ws.title = 'Attendance'
    ws.append(['Name', 'Phone', 'Employee ID', 'Date', 'IN', 'OUT', 'IN Image', 'OUT Image'])
    for r in rows:
        ws.append([r['name'], r['phone'], r['employee_id'], r['date'], r['in_time'], r['out_time'], r['in_image'], r['out_image']])

    stream = BytesIO()
    wb.save(stream)
    stream.seek(0)
    return send_file(stream,
                     as_attachment=True,
                     download_name='attendance_export.xlsx',
                     mimetype='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')


if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=False)
