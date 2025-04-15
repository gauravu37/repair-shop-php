import { Component, OnInit } from '@angular/core';
import { CommonModule, NgIf } from '@angular/common';
import { HttpClientModule } from '@angular/common/http'; // Add this import
import { RouterLink } from '@angular/router';

import { 
  RowComponent, 
  ColComponent, 
  TextColorDirective, 
  CardComponent, 
  CardHeaderComponent, 
  CardBodyComponent, 
  TableDirective 
} from '@coreui/angular';
import { JobService } from '../../../services/job.service';


@Component({
    selector: 'app-jobs',
    templateUrl: './jobs.component.html',
    styleUrls: ['./jobs.component.scss'],
    standalone: true,
    imports: [
        CommonModule,
        HttpClientModule, // Add this line
        RowComponent, 
        ColComponent, 
        TextColorDirective, 
        CardComponent, 
        CardHeaderComponent, 
        CardBodyComponent, 
        TableDirective,
        RouterLink
    ],
    providers: [JobService]
})
export class JobsComponent implements OnInit {
    jobs: any[] = [];
    isLoading = true;
  
    constructor(private jobService: JobService) {}
    
    ngOnInit(): void {
        this.loadJobs();
    }
    
    loadJobs() {
        this.jobService.getJobs().subscribe({
            next: (response: any) => {
              console.log(response);
                this.jobs = response.data || response;
                this.isLoading = false;
            },
            error: (err) => {
                console.error('Error loading jobs:', err);
                this.isLoading = false;
            }
        });
    }
}