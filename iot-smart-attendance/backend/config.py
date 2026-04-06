import os

BASE_DIR = os.path.dirname(os.path.abspath(__file__))

class Config:
    SECRET_KEY = os.environ.get('SECRET_KEY', 'change-me')
    DATABASE = os.path.join(BASE_DIR, 'instance', 'attendance.db')
    IMAGE_DIR = os.path.join(BASE_DIR, 'saved_faces')
    FACE_REQUIRED = os.environ.get('FACE_REQUIRED', 'false').lower() == 'true'
    ENABLE_GSM = os.environ.get('ENABLE_GSM', 'false').lower() == 'true'
    GSM_PORT = os.environ.get('GSM_PORT', '/dev/ttyUSB0')
    GSM_BAUD = int(os.environ.get('GSM_BAUD', '9600'))
    BUZZER_PIN = int(os.environ.get('BUZZER_PIN', '18'))
