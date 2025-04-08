// src/app/components/job-create/job-create.component.ts
import { Component, OnInit } from '@angular/core';
import { ApiService } from '../../services/api.service';
import { FormBuilder, FormGroup, FormArray, Validators } from '@angular/forms';

@Component({
  selector: 'app-job-create',
  templateUrl: './job-create.component.html',
  styleUrls: ['./job-create.component.css']
})
export class JobCreateComponent implements OnInit {
  jobForm: FormGroup;
  users: any[] = [];
  items: any[] = [];
  availableItems: any[] = [];

  constructor(
    private apiService: ApiService,
    private fb: FormBuilder
  ) {
    this.jobForm = this.fb.group({
      customer_id: ['', Validators.required],
      items: this.fb.array([])
    });
  }

  ngOnInit(): void {
    this.loadUsers();
    this.loadItems();
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

  loadItems(): void {
    this.apiService.getItems().subscribe(
      (data: any) => {
        this.items = data.records;
        this.availableItems = [...this.items];
      },
      (error) => {
        console.error('Error loading items:', error);
      }
    );
  }

  get itemForms() {
    return this.jobForm.get('items') as FormArray;
  }

  addItem(): void {
    const itemGroup = this.fb.group({
      item_id: ['', Validators.required],
      quantity: [1, [Validators.required, Validators.min(1)]],
      price: ['', Validators.required],
      total: [0]
    });

    this.itemForms.push(itemGroup);
  }

  removeItem(index: number): void {
    this.itemForms.removeAt(index);
    this.updateAvailableItems();
  }

  onItemSelect(index: number): void {
    const selectedItemId = this.itemForms.at(index).get('item_id')?.value;
    const selectedItem = this.items.find(item => item.id == selectedItemId);
    
    if (selectedItem) {
      this.itemForms.at(index).get('price')?.setValue(selectedItem.price);
      this.calculateTotal(index);
    }
    this.updateAvailableItems();
  }

  calculateTotal(index: number): void {
    const quantity = this.itemForms.at(index).get('quantity')?.value;
    const price = this.itemForms.at(index).get('price')?.value;
    const total = quantity * price;
    
    this.itemForms.at(index).get('total')?.setValue(total);
  }

  updateAvailableItems(): void {
    const selectedItemIds = this.itemForms.controls
      .map(control => control.get('item_id')?.value)
      .filter(id => id !== '');
    
    this.availableItems = this.items.filter(item => 
      !selectedItemIds.includes(item.id.toString())
    );
  }

  onSubmit(): void {
    if (this.jobForm.valid) {
      const formData = this.jobForm.value;
      const jobData = {
        customer_id: formData.customer_id,
        items: formData.items.map((item: any) => ({
          id: item.item_id,
          quantity: item.quantity,
          price: item.price
        }))
      };

      this.apiService.createJob(jobData).subscribe(
        (response) => {
          alert('Job created successfully!');
          // Reset form or navigate
        },
        (error) => {
          console.error('Error creating job:', error);
        }
      );
    }
  }
}