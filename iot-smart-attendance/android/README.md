# Android App (Kotlin)

## Screens
- LoginActivity: phone + password login
- DashboardActivity: biometric auth + camera capture + API submit

## Flow
1. Login (`/login`) and save `employee_id` in SharedPreferences.
2. Tap **Mark Attendance**.
3. Fingerprint (BiometricPrompt) must succeed.
4. Camera opens, photo captured.
5. Image converted to Base64 and sent to `/mark_api`.

Set backend base URL in `api/RetrofitClient.kt`.
