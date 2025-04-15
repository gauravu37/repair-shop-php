import { Component, OnInit } from '@angular/core';
import { DocsExampleComponent } from '@docs-components/public-api'
import { FormsModule, FormBuilder, FormGroup, Validators, FormArray, ReactiveFormsModule } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { CommonModule } from '@angular/common';
import { JobService } from '../../../services/job.service';
import { HttpClientModule } from '@angular/common/http'; // Add this import

import { 
  RowComponent, ColComponent, CardComponent, CardHeaderComponent, CardBodyComponent,
  FormControlDirective, FormDirective, FormLabelDirective, ButtonDirective,
  InputGroupComponent, InputGroupTextDirective, 
  TextColorDirective,FormSelectDirective, FormCheckComponent, FormCheckInputDirective, FormCheckLabelDirective, ColDirective
} from '@coreui/angular';

interface Device {
  device_type: string;
  problem_description: string;
  estimated_price: number;
  id?: number; // Optional for new devices
}

interface Job {
  id: number;
  user_id: number;
  job_date: string;
  item_type: string;
  problem_description: string;
  estimated_delivery: string;
  estimated_price: number;
  status: string;
  devices: Device[];
}

interface JobRequest {
  user_id: number;
  estimated_delivery: string;
  estimated_price: number;
  devices: Device[];
  item_type: string;
  problem_description: string;
  status?: string; // Optional for new jobs
}

@Component({
  selector: 'app-addjob',
  templateUrl: './addjob.component.html',
  styleUrls: ['./addjob.component.scss'],
  standalone: true,
  imports: [
    CommonModule,
    HttpClientModule, // Add this line
    ReactiveFormsModule,
    RowComponent, ColComponent,
    CardComponent, CardHeaderComponent, CardBodyComponent,
    FormControlDirective, FormDirective, FormLabelDirective,
    ButtonDirective,
    InputGroupComponent, InputGroupTextDirective,
    RowComponent, ColComponent, TextColorDirective, CardComponent, CardHeaderComponent, CardBodyComponent, DocsExampleComponent, FormControlDirective, ReactiveFormsModule, FormsModule, FormDirective, FormLabelDirective, FormSelectDirective, FormCheckComponent, FormCheckInputDirective, FormCheckLabelDirective, ButtonDirective, ColDirective, InputGroupComponent, InputGroupTextDirective
  ],
  providers: [JobService]
})
export class AddjobComponent implements OnInit{
  jobForm: FormGroup = this.fb.group({
    user_id: ['', Validators.required],
    devices: this.fb.array([
      this.createDeviceFormGroup()
    ]),
    estimated_delivery: ['', Validators.required]
  });

  isEditMode = false;
  jobId: number | null = null;

  customers: any[] = [];
  itemTypes = ['laptop', 'desktop', 'printer', 'monitor', 'phone', 'tablet', 'other'];
  minDate: string = new Date().toISOString().split('T')[0];

  constructor(
    private fb: FormBuilder,
    private jobService: JobService,
    private router: Router,
    private route: ActivatedRoute
  ) {
    this.jobForm = this.fb.group({
      user_id: ['', Validators.required],
      devices: this.fb.array([this.createDeviceFormGroup()]),
      estimated_delivery: ['', Validators.required],
      status: ['pending'] // Added for edit mode
    });
  }

  ngOnInit() {
    this.loadCustomers();
    
    // Check for edit mode
    this.route.paramMap.subscribe(params => {
      const id = params.get('id');
      if (id) {
        this.isEditMode = true;
        this.jobId = +id;
        this.loadJob(this.jobId);
      }
    });
  }

  // NEW METHOD: Load job for editing
  loadJob(id: number) {
    this.jobService.getJob(id).subscribe({
      next: (job: Job) => {  // Explicitly type the job parameter
        // Clear existing devices
        while (this.devices.length) {
          this.devices.removeAt(0);
        }
        
        // Patch the main form values
        this.jobForm.patchValue({
          user_id: job.user_id,
          estimated_delivery: job.estimated_delivery,
          status: job.status
        });
        
        // Add devices with proper typing
        job.devices.forEach((device: Device) => {  // Explicitly type device parameter
          this.devices.push(this.fb.group({
            device_type: [device.device_type, Validators.required],
            problem_description: [device.problem_description, 
                                 [Validators.required, Validators.minLength(10)]],
            estimated_price: [device.estimated_price, 
                            [Validators.required, Validators.min(0)]]
          }));
        });
      },
      error: (err) => console.error('Error loading job:', err)
    });
  }

  get devices(): FormArray {
    return this.jobForm.get('devices') as FormArray;
  }

  createDeviceFormGroup(): FormGroup {
    return this.fb.group({
      device_type: ['', Validators.required],
      problem_description: ['', [Validators.required, Validators.minLength(10)]],
      estimated_price: ['', [Validators.required, Validators.min(0)]]
    });
  }

  addDevice(): void {
    this.devices.push(this.createDeviceFormGroup());
  }

  removeDevice(index: number): void {
    this.devices.removeAt(index);
  }

  loadCustomers(): void {
    this.jobService.getCustomers().subscribe({
      next: (response) => this.customers = response.records || response,
      error: (err) => console.error('Error loading customers:', err)
    });
  }

  calculateTotalPrice(): number {
    return this.devices.controls.reduce((sum, device) => {
      return sum + (+device.get('estimated_price')?.value || 0);
    }, 0);
  }

  /*
  onSubmit(): void {
    alert(this.jobForm.valid );
    if (this.jobForm.valid && this.devices.length > 0) {
      const firstDevice = this.jobForm.value.devices[0];
      
      const jobData: JobRequest = {
        user_id: this.jobForm.value.user_id,
        estimated_delivery: this.jobForm.value.estimated_delivery,
        estimated_price: this.calculateTotalPrice(),
        devices: this.jobForm.value.devices,
        item_type: firstDevice.device_type,
        problem_description: firstDevice.problem_description
      };

      this.jobService.createJob(jobData).subscribe({
        next: () => {
          alert('Job created successfully!');
          this.router.navigate(['base/jobs']);
        },
        error: (err) => {
          console.error('Error creating job:', err);
          alert('Error creating job. Please try again.');
        }
      });
    } else {
      alert('Please add at least one device and fill all required fields');
    }
  }*/


  onSubmit(): void {
    //alert(this.devices.length);
    //alert(this.jobForm.valid);
    // Mark all form controls as touched to trigger validation messages
    this.markFormGroupTouched(this.jobForm);

    // Check if form is valid and at least one device exists
    if (this.jobForm.invalid) {
      //alert('Please fill all required fields');
      //return;
    }

    if (this.devices.length === 0) {
      alert('Please add at least one device');
      return;
    }

    if (this.devices.length > 0) {
      const firstDevice = this.jobForm.value.devices[0];
      
      const jobData = {
        user_id: this.jobForm.value.user_id,
        estimated_delivery: this.jobForm.value.estimated_delivery,
        estimated_price: this.calculateTotalPrice(),
        devices: this.jobForm.value.devices,
        item_type: firstDevice.device_type,
        problem_description: firstDevice.problem_description,
        ...(this.isEditMode && { status: this.jobForm.value.status }) // Include status in edit mode
      };

      const observable = this.isEditMode && this.jobId 
        ? this.jobService.updateJob(this.jobId, jobData)
        : this.jobService.createJob(jobData);

      observable.subscribe({
        next: () => {
          alert(`Job ${this.isEditMode ? 'updated' : 'created'} successfully!`);
          this.router.navigate(['base/jobs']);
        },
        error: (err) => {
          console.error(`Error ${this.isEditMode ? 'updating' : 'creating'} job:`, err);
          alert(`Error ${this.isEditMode ? 'updating' : 'creating'} job. Please try again.`);
        }
      });
    } else {
      alert('Please add at least one device and fill all required fields');
    }
  }

  // Helper method to mark all form controls as touched
  private markFormGroupTouched(formGroup: FormGroup | FormArray) {
    Object.values(formGroup.controls).forEach(control => {
      control.markAsTouched();

      if (control instanceof FormGroup || control instanceof FormArray) {
        this.markFormGroupTouched(control);
      }
    });
  }
}