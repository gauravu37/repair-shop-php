// src/app/services/api.service.ts
import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from 'src/environments/environment';


@Injectable({
  providedIn: 'root'
})
export class ApiService {
  private apiUrl = environment.apiUrl;

  private user: any = null;
  private userId: number | null = null;

  constructor(private http: HttpClient) {
    const userData = localStorage.getItem('user');
    this.user = userData ? JSON.parse(userData) : null;
    this.userId = this.user?.id || null;
  }

  getCurrentUserId(): number | null {
    return this.userId;
  }
  
    // auth.service.ts
	login(email: string, password: string): Observable<any> {
	  return this.http.post(`${this.apiUrl}/login.php`, { email, password });
	}

	getProtectedData(): Observable<any> {
	  const headers = new HttpHeaders({
		'Authorization': `Bearer ${this.getToken()}`
	  });
	  return this.http.get(`${this.apiUrl}/protected.php`, { headers });
	}

	private getToken(): string {
	  return localStorage.getItem('auth_token') || '';
	}

    // User methods
  getJobs(): Observable<any> {
    return this.http.get(`${this.apiUrl}/jobs.php`);
  }

  // User methods
  getUsers(): Observable<any> {
    return this.http.get(`${this.apiUrl}/users.php`);
  }

  // User methods
  getTotalUsers(): Observable<any> {
    return this.http.get(`${this.apiUrl}/users.php?totalusers=1`);
  }

  createUser(user: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/users.php`, user);
  }

  // Job methods
  createJob(job: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/jobs.php`, job);
  }

  getJob(id: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/jobs.php?id=${id}`);
  }

  // Item methods
  getItems(): Observable<any> {
    return this.http.get(`${this.apiUrl}/items.php`);
  }
  
}