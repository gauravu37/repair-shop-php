// src/app/services/auth.service.ts
import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { BehaviorSubject, Observable, tap } from 'rxjs';
import { Router } from '@angular/router';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private apiUrl = 'http://localhost/computer_repair_php/repair-shop-php/api'; // Update with your API URL
  private loggedIn = new BehaviorSubject<boolean>(false);

  constructor(private http: HttpClient, private router: Router) {}

  login(email: string, password: string): Observable<any> {
    return this.http.post(`${this.apiUrl}/login.php`, { email, password }).pipe(
      tap((response: any) => {
        if (response.status === 'success') {
          localStorage.setItem('auth_token', response.token);
          localStorage.setItem('user', JSON.stringify(response.user));
          this.loggedIn.next(true);
        }
      })	
    );
  }

  isLoggedIn(): boolean {
    const token = localStorage.getItem('auth_token');
    const user = localStorage.getItem('user');
    return !!token && !!user;
  }

  getCurrentUser(): any {
    const user = localStorage.getItem('user');
    return user ? JSON.parse(user) : null;
  }

  logout(): void {
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
    this.loggedIn.next(false);
    this.router.navigate(['/login']);
  }

  getToken(): string | null {
    return localStorage.getItem('auth_token');
  }

  getUser(): any {
    const user = localStorage.getItem('user');
    return user ? JSON.parse(user) : null;
  }

  getCases(): any {
    const user = localStorage.getItem('user');
    return user ? JSON.parse(user) : null;
  }
}