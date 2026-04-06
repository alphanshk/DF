package com.example.iotattendance.model

data class LoginRequest(val phone: String, val password: String)
data class LoginResponse(val message: String?, val employee_id: Int?, val name: String?, val error: String?)

data class MarkAttendanceRequest(val employee_id: Int, val image_base64: String)
data class MarkAttendanceResponse(val message: String?, val status: String?, val error: String?)
