package com.example.iotattendance.ui

import android.content.Intent
import android.os.Bundle
import android.widget.Button
import android.widget.EditText
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.example.iotattendance.R
import com.example.iotattendance.api.RetrofitClient
import com.example.iotattendance.model.LoginRequest
import com.example.iotattendance.model.LoginResponse
import com.example.iotattendance.util.SessionManager
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class LoginActivity : AppCompatActivity() {
    private lateinit var session: SessionManager

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_login)

        session = SessionManager(this)
        if (session.getEmployeeId() > 0) {
            startActivity(Intent(this, DashboardActivity::class.java))
            finish()
        }

        val phoneEt = findViewById<EditText>(R.id.etPhone)
        val passEt = findViewById<EditText>(R.id.etPassword)
        findViewById<Button>(R.id.btnLogin).setOnClickListener {
            val body = LoginRequest(phoneEt.text.toString().trim(), passEt.text.toString())
            RetrofitClient.apiService.login(body).enqueue(object : Callback<LoginResponse> {
                override fun onResponse(call: Call<LoginResponse>, response: Response<LoginResponse>) {
                    val data = response.body()
                    if (response.isSuccessful && data?.employee_id != null) {
                        session.saveSession(data.employee_id, data.name ?: "")
                        startActivity(Intent(this@LoginActivity, DashboardActivity::class.java))
                        finish()
                    } else {
                        Toast.makeText(this@LoginActivity, data?.error ?: "Login failed", Toast.LENGTH_SHORT).show()
                    }
                }

                override fun onFailure(call: Call<LoginResponse>, t: Throwable) {
                    Toast.makeText(this@LoginActivity, "Network error: ${t.message}", Toast.LENGTH_SHORT).show()
                }
            })
        }
    }
}
