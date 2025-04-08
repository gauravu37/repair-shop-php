import { Component } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { JobService } from '../../../services/job.service';
import { Router } from '@angular/router';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule } from '@angular/forms';
import { DocsExampleComponent } from '@docs-components/public-api';

import { HttpClientModule } from '@angular/common/http'; // Add this import
import { RowComponent, ColComponent, TextColorDirective, CardComponent, CardHeaderComponent, CardBodyComponent, FormControlDirective, FormDirective, FormLabelDirective, FormSelectDirective, FormCheckComponent, FormCheckInputDirective, FormCheckLabelDirective, ButtonDirective, ColDirective, InputGroupComponent, InputGroupTextDirective } from '@coreui/angular';

@Component({
  selector: 'app-new-job',
  templateUrl: './new-job.component.html',
  styleUrls: ['./new-job.component.scss'],
  standalone: true,
  
  imports: [RowComponent, ColComponent, TextColorDirective, CardComponent, CardHeaderComponent, CardBodyComponent, FormControlDirective, ReactiveFormsModule, FormDirective, FormLabelDirective, FormSelectDirective, FormCheckComponent, FormCheckInputDirective, FormCheckLabelDirective, ButtonDirective, ColDirective, InputGroupComponent, InputGroupTextDirective,
    CommonModule,
    DocsExampleComponent,
    HttpClientModule, // Add this line
    ReactiveFormsModule,
    CardComponent,
    CardHeaderComponent,
    CardBodyComponent,
    FormDirective,
    FormControlDirective,
    InputGroupComponent,
    InputGroupTextDirective,
    ButtonDirective
  ],
})
export class NewJobComponent {
  //jobForm: FormGroup;
  jobForm!: FormGroup;  // Note the ! operator
  customers: any[] = [];
  itemTypes = ['laptop', 'desktop', 'printer', 'monitor', 'phone', 'tablet', 'other'];
  minDate: string;

  constructor(
    private fb: FormBuilder,
    private jobService: JobService,
    private router: Router
  ) {
    this.minDate = new Date().toISOString().split('T')[0];
    this.initializeForm();
    this.loadCustomers();
  }

  initializeForm() {
    this.jobForm = this.fb.group({
      user_id: ['', Validators.required],
      item_type: ['', Validators.required],
      problem_description: ['', [Validators.required, Validators.minLength(10)]],
      estimated_delivery: ['', Validators.required],
      estimated_price: ['', [Validators.required, Validators.min(0)]]
    });
  }

  loadCustomers() {
    this.jobService.getCustomers().subscribe({
      next: (response: any) => {
        this.customers = response.records || response;
      },
      error: (err) => {
        console.error('Error loading customers:', err);
      }
    });
  }

  onSubmit() {
    if (this.jobForm.valid) {
      this.jobService.createJob(this.jobForm.value).subscribe({
        next: () => {
          alert('Job created successfully!');
          this.router.navigate(['/jobs']);
        },
        error: (err) => {
          console.error('Error creating job:', err);
          alert('Error creating job. Please try again.');
        }
      });
    }
  }
}