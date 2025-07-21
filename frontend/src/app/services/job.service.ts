import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from 'src/environments/environment';

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
  //private apiUrl = 'http://localhost/computer_repair_php/repair-shop-php/api/jobs.php';

  private apiUrl = environment.apiUrl+'/jobs.php';

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

  getJobs(): Observable<Job[]> {
    return this.http.get<Job[]>(this.apiUrl);
  }

  // Add this new method to get a single job
  getJob(id: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/?id=${id}&payment_info=true`);
  }

  getTotalPaidPayment(): Observable<any> {
    return this.http.get(`${this.apiUrl}/?totalpayment=paid`);
  }

  getTotalPendingPayment(): Observable<any> {
    return this.http.get(`${this.apiUrl}/?totalpayment=pending`);
  }

  /* Dashboad All Jobs Section
   * 
   */

  //Get All Total Jobs Count
  getJobsCountTotal(): Observable<any> {
    return this.http.get(`${this.apiUrl}/?totalJobsCount=all`);
  }

  //Get Pending Jobs Count
  getJobsCountPending(): Observable<any> {
    return this.http.get(`${this.apiUrl}/?totalJobsCount=pending`);
  }

  //Get Progress Jobs Count
  getJobsCountProgress(): Observable<any> {
    return this.http.get(`${this.apiUrl}/?totalJobsCount=in_progress`);
  }

  //Get Completed Jobs Count
  getJobsCountCompleted(): Observable<any> {
    return this.http.get(`${this.apiUrl}/?totalJobsCount=completed`);
  }

  // Month Wise //

  //Get All Total Jobs Count
  getJobsCountCurrentTotal(): Observable<any> {
    return this.http.get(`${this.apiUrl}/?totalJobsCount=all&month=all`);
  }

  //Get Pending Jobs Count
  getJobsCountCurrentPending(): Observable<any> {
    return this.http.get(`${this.apiUrl}/?totalJobsCount=pending&month=pending`);
  }

  //Get Progress Jobs Count
  getJobsCountCurrentProgress(): Observable<any> {
    return this.http.get(`${this.apiUrl}/?totalJobsCount=in_progress&month=progress`);
  }

  //Get Completed Jobs Count
  getJobsCountCurrentCompleted(): Observable<any> {
    return this.http.get(`${this.apiUrl}/?totalJobsCount=completed&month=completed`);
  }
  /* -------------------- */

  createJob(jobData: Job): Observable<any> {
    return this.http.post(this.apiUrl, jobData);
  }

  updateJob(id: number, jobData: any): Observable<any> {
    return this.http.put(`${this.apiUrl}/?id=${id}`, jobData);
  }

  generateInvoice(jobId: number): Observable<any> {
    return this.http.get(`${this.apiUrl}?id=${jobId}&invoice=true`);
  }

  getCustomers(): Observable<any> {
    //return this.http.get('http://localhost/computer_repair_php/repair-shop-php/api/users.php');
    return this.http.get(`${environment.apiUrl}/users.php`);
  }

  createCustomer(customerData: any): Observable<any> {
    return this.http.post<any>(`${environment.apiUrl}/users.php`, customerData);
  }
}