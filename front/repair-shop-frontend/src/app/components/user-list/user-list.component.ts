// src/app/components/user-list/user-list.component.ts
import { Component, OnInit } from '@angular/core';
import { ApiService } from '../../services/api.service';

@Component({
  selector: 'app-user-list',
  templateUrl: './user-list.component.html',
  styleUrls: ['./user-list.component.css']
})
export class UserListComponent implements OnInit {
  users: any[] = [];

  constructor(private apiService: ApiService) { }

  ngOnInit(): void {
    this.loadUsers();
  }

  loadUsers(): void {
    this.apiService.getUsers().subscribe(
      (data: any) => {
        this.users = data.records;
      },
      (error) => {
        console.error('Error loading users:', error);
      }
    );
  }
}