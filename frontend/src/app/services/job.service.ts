import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface Job {
  id?: number;
  user_id: number;
  full_name?: string;
  job_date?: string;
  item_type: string;
  problem_description: string;
  estimated_delivery: string;
  estimated_price: number;
  status?: string;
}

@Injectable({
  providedIn: 'root'
})
export class JobService {
  private apiUrl = 'http://localhost/computer_repair_php/repair-shop-php/api/jobs.php';

  constructor(private http: HttpClient) { }

  getJobs(): Observable<Job[]> {
    return this.http.get<Job[]>(this.apiUrl);
  }

  createJob(jobData: Job): Observable<any> {
    return this.http.post(this.apiUrl, jobData);
  }
  getCustomers(): Observable<any> {
    return this.http.get('http://localhost/computer_repair_php/repair-shop-php/api/users.php');
  }
}