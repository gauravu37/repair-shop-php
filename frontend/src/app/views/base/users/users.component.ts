import { DocsExampleComponent } from '@docs-components/public-api';
import { Component, OnInit } from '@angular/core';
import { CommonModule, NgIf } from '@angular/common';
import { RowComponent, ColComponent, TextColorDirective, CardComponent, CardHeaderComponent, CardBodyComponent, TableDirective, TableColorDirective, TableActiveDirective, BorderDirective, AlignDirective } from '@coreui/angular';
import { ApiService } from '../../../services/api.service';
import { HttpClientModule } from '@angular/common/http';

@Component({
    selector: 'app-users',
    templateUrl: './users.component.html',
    styleUrls: ['./users.component.scss'],
    imports: [CommonModule, HttpClientModule, RowComponent, ColComponent, TextColorDirective, CardComponent, CardHeaderComponent, CardBodyComponent, DocsExampleComponent, TableDirective, TableColorDirective, TableActiveDirective, BorderDirective, AlignDirective],
    providers: [ApiService]
})
export class UsersComponent implements OnInit{
	users: any[] = [];		
	constructor(private apiService: ApiService) {}
    ngOnInit(): void {
        this.apiService.getUsers().subscribe({
            next: (res) => {
              this.users = res.records || []; // handle missing or empty records
            },
            error: (err) => {
              console.error('Error fetching users:', err);
            }
        });
    }      
}
