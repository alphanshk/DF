package com.example.iotattendance.util

import android.content.Context

class SessionManager(context: Context) {
    private val prefs = context.getSharedPreferences("iot_session", Context.MODE_PRIVATE)

    fun saveSession(employeeId: Int, name: String) {
        prefs.edit().putInt("employee_id", employeeId).putString("name", name).apply()
    }

    fun getEmployeeId(): Int = prefs.getInt("employee_id", -1)
    fun getName(): String = prefs.getString("name", "") ?: ""
    fun clear() = prefs.edit().clear().apply()
}
