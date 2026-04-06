import base64
import os
from datetime import datetime


def save_base64_image(image_base64: str, employee_id: int, image_dir: str) -> str:
    os.makedirs(image_dir, exist_ok=True)
    ts = datetime.now().strftime('%Y%m%d_%H%M%S')
    filename = f"{employee_id}_{ts}.jpg"
    file_path = os.path.join(image_dir, filename)

    image_data = image_base64.split(',')[-1]
    with open(file_path, 'wb') as f:
        f.write(base64.b64decode(image_data))

    return filename


def mock_face_verify(employee_id: int, filename: str) -> bool:
    """Placeholder for optional face recognition."""
    return True
