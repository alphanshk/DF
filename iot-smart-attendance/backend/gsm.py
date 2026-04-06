import time

try:
    import serial
except Exception:
    serial = None


def send_sms(port: str, baud: int, number: str, message: str) -> bool:
    """Send SMS via SIM800L. Returns True on success."""
    if serial is None:
        return False
    try:
        modem = serial.Serial(port, baud, timeout=1)
        time.sleep(1)
        modem.write(b'AT\r')
        time.sleep(0.5)
        modem.write(b'AT+CMGF=1\r')
        time.sleep(0.5)
        modem.write(f'AT+CMGS="{number}"\r'.encode())
        time.sleep(0.5)
        modem.write(message.encode() + b'\x1A')
        time.sleep(2)
        modem.close()
        return True
    except Exception:
        return False
