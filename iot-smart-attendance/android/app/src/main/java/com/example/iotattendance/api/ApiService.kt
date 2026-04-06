package com.example.iotattendance.api

import com.example.iotattendance.model.LoginRequest
import com.example.iotattendance.model.LoginResponse
import com.example.iotattendance.model.MarkAttendanceRequest
import com.example.iotattendance.model.MarkAttendanceResponse
import retrofit2.Call
import retrofit2.http.Body
import retrofit2.http.POST

interface ApiService {
    @POST("login")
    fun login(@Body body: LoginRequest): Call<LoginResponse>

    @POST("mark_api")
    fun markAttendance(@Body body: MarkAttendanceRequest): Call<MarkAttendanceResponse>
}
