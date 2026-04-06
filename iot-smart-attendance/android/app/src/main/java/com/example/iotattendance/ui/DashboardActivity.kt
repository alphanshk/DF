package com.example.iotattendance.ui

import android.app.Activity
import android.content.Intent
import android.graphics.Bitmap
import android.os.Bundle
import android.util.Base64
import android.widget.Button
import android.widget.TextView
import android.widget.Toast
import androidx.activity.result.contract.ActivityResultContracts
import androidx.appcompat.app.AppCompatActivity
import androidx.biometric.BiometricPrompt
import androidx.core.content.ContextCompat
import com.example.iotattendance.R
import com.example.iotattendance.api.RetrofitClient
import com.example.iotattendance.model.MarkAttendanceRequest
import com.example.iotattendance.model.MarkAttendanceResponse
import com.example.iotattendance.util.SessionManager
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response
import java.io.ByteArrayOutputStream

class DashboardActivity : AppCompatActivity() {
    private lateinit var session: SessionManager

    private val cameraLauncher = registerForActivityResult(ActivityResultContracts.StartActivityForResult()) { result ->
        if (result.resultCode == Activity.RESULT_OK) {
            val bmp = result.data?.extras?.get("data") as? Bitmap
            if (bmp != null) sendAttendance(bmp)
            else Toast.makeText(this, "Failed to capture image", Toast.LENGTH_SHORT).show()
        }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_dashboard)

        session = SessionManager(this)
        val employeeId = session.getEmployeeId()
        if (employeeId <= 0) {
            startActivity(Intent(this, LoginActivity::class.java))
            finish()
            return
        }

        findViewById<TextView>(R.id.tvWelcome).text = "Welcome, ${session.getName()} (ID: $employeeId)"

        findViewById<Button>(R.id.btnMark).setOnClickListener {
            authenticateBiometricThenOpenCamera()
        }

        findViewById<Button>(R.id.btnLogout).setOnClickListener {
            session.clear()
            startActivity(Intent(this, LoginActivity::class.java))
            finish()
        }
    }

    private fun authenticateBiometricThenOpenCamera() {
        val executor = ContextCompat.getMainExecutor(this)
        val prompt = BiometricPrompt(this, executor, object : BiometricPrompt.AuthenticationCallback() {
            override fun onAuthenticationSucceeded(result: BiometricPrompt.AuthenticationResult) {
                super.onAuthenticationSucceeded(result)
                val cameraIntent = Intent(android.provider.MediaStore.ACTION_IMAGE_CAPTURE)
                cameraLauncher.launch(cameraIntent)
            }

            override fun onAuthenticationError(errorCode: Int, errString: CharSequence) {
                Toast.makeText(this@DashboardActivity, "Auth error: $errString", Toast.LENGTH_SHORT).show()
            }

            override fun onAuthenticationFailed() {
                Toast.makeText(this@DashboardActivity, "Fingerprint not recognized", Toast.LENGTH_SHORT).show()
            }
        })

        val promptInfo = BiometricPrompt.PromptInfo.Builder()
            .setTitle("Verify Fingerprint")
            .setSubtitle("Authenticate before marking attendance")
            .setNegativeButtonText("Cancel")
            .build()

        prompt.authenticate(promptInfo)
    }

    private fun sendAttendance(bitmap: Bitmap) {
        val stream = ByteArrayOutputStream()
        bitmap.compress(Bitmap.CompressFormat.JPEG, 90, stream)
        val base64 = Base64.encodeToString(stream.toByteArray(), Base64.NO_WRAP)

        val request = MarkAttendanceRequest(session.getEmployeeId(), base64)
        RetrofitClient.apiService.markAttendance(request).enqueue(object : Callback<MarkAttendanceResponse> {
            override fun onResponse(call: Call<MarkAttendanceResponse>, response: Response<MarkAttendanceResponse>) {
                val body = response.body()
                if (response.isSuccessful && body?.error == null) {
                    Toast.makeText(this@DashboardActivity, body.message ?: "Attendance marked", Toast.LENGTH_SHORT).show()
                } else {
                    Toast.makeText(this@DashboardActivity, body?.error ?: "Failed", Toast.LENGTH_SHORT).show()
                }
            }

            override fun onFailure(call: Call<MarkAttendanceResponse>, t: Throwable) {
                Toast.makeText(this@DashboardActivity, "Network error: ${t.message}", Toast.LENGTH_SHORT).show()
            }
        })
    }
}
