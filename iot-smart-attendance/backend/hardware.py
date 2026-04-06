import time

try:
    import RPi.GPIO as GPIO
    GPIO_AVAILABLE = True
except Exception:
    GPIO_AVAILABLE = False


class Buzzer:
    def __init__(self, pin: int):
        self.pin = pin
        self.enabled = GPIO_AVAILABLE
        if self.enabled:
            GPIO.setmode(GPIO.BCM)
            GPIO.setwarnings(False)
            GPIO.setup(self.pin, GPIO.OUT)

    def _beep(self, duration=0.12):
        if not self.enabled:
            return
        GPIO.output(self.pin, True)
        time.sleep(duration)
        GPIO.output(self.pin, False)

    def beep_success(self):
        """Short beep for success."""
        self._beep(0.10)

    def beep_error(self):
        """3 beeps for failure/reject."""
        for _ in range(3):
            self._beep(0.10)
            time.sleep(0.08)

    def cleanup(self):
        if self.enabled:
            GPIO.cleanup(self.pin)
