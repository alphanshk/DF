# IoT Smart Employee Attendance System

Complete project with:
- **Android app (Kotlin)** for login, biometric auth, camera capture, Base64 upload.
- **Flask backend (Raspberry Pi)** for attendance logic, image storage, buzzer, optional GSM SMS, and Excel export.

## Folder Structure

```
iot-smart-attendance/
├── backend/
│   ├── app.py
│   ├── config.py
│   ├── db.py
│   ├── gsm.py
│   ├── hardware.py
│   ├── utils.py
│   ├── requirements.txt
│   ├── requirements-1gb.txt
│   ├── saved_faces/
│   ├── instance/
│   └── templates/dashboard.html
└── android/
    ├── settings.gradle.kts
    ├── build.gradle.kts
    └── app/
        ├── build.gradle.kts
        └── src/main/
            ├── AndroidManifest.xml
            ├── java/com/example/iotattendance/
            │   ├── api/
            │   ├── model/
            │   ├── ui/
            │   └── util/
            └── res/layout/
```

## Backend API
- `POST /login` → login with phone + password.
- `POST /mark_api` → mark attendance with `{employee_id, image_base64}`.
- `GET /attendance` → attendance JSON.
- `GET /` → web dashboard.
- `GET /export` → Excel export.

## Attendance Rules
- 1st scan in day = **IN**
- 2nd scan in day = **OUT**
- 3rd scan = **REJECTED**

## Raspberry Pi Setup
```bash
cd backend
python3 -m venv .venv
source .venv/bin/activate
pip install -r requirements-1gb.txt
python app.py
```

Environment variables:
- `FACE_REQUIRED=false` (default)
- `ENABLE_GSM=false` (default)
- `BUZZER_PIN=18`
- `GSM_PORT=/dev/ttyUSB0`
- `GSM_BAUD=9600`

### Demo user creation
```bash
curl -X POST http://<pi-ip>:5000/seed \
  -H "Content-Type: application/json" \
  -d '{"name":"Alphan","phone":"9876543210","password":"123456"}'
```

## Android Setup
1. Open `android/` in Android Studio.
2. Update `BASE_URL` in `RetrofitClient.kt` with Raspberry Pi IP.
3. Build and run on physical device.
4. Login, fingerprint verify, capture image, mark attendance.
