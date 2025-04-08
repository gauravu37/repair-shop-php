// src/app/services/api.service.ts
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class ApiService {
  private apiUrl = 'http://localhost/computer_repair_php/repair-shop-php/api';

  constructor(private http: HttpClient) { }

  // User methods
  getUsers(): Observable<any> {
    return this.http.get(`${this.apiUrl}/users.php`);
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