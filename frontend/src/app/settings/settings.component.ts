import { Component, OnInit } from '@angular/core';
import { DocsExampleComponent } from '@docs-components/public-api'
import { FormsModule, FormBuilder, FormGroup, Validators, FormArray, ReactiveFormsModule } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { CommonModule } from '@angular/common';
import { SettingsService } from '../services/settings.service';
import { finalize } from 'rxjs/operators';
import { HttpClientModule } from '@angular/common/http'; // Add this import
import { ModalModule } from '@coreui/angular';
import { 
  RowComponent, ColComponent, CardComponent, CardHeaderComponent, CardBodyComponent,
  FormControlDirective, FormDirective, FormLabelDirective, ButtonDirective,
  InputGroupComponent, InputGroupTextDirective, 
  TextColorDirective,FormSelectDirective, FormCheckComponent, SpinnerComponent,FormCheckInputDirective, FormCheckLabelDirective, ColDirective
} from '@coreui/angular';
@Component({
  selector: 'app-settings',
  standalone: true,
  imports: [
    // CoreUI Components
    CardComponent,
    CardHeaderComponent,
    CardBodyComponent,
    FormControlDirective,
    FormLabelDirective,
    ButtonDirective,
    SpinnerComponent,
    
    // Angular Forms
    ReactiveFormsModule
  ],
  templateUrl: './settings.component.html',
  styleUrls: ['./settings.component.scss']
})
export class SettingsComponent implements OnInit {
  settingsForm: FormGroup;
  logoPreview: string | ArrayBuffer | null = null;
  qrCodePreview: string | ArrayBuffer | null = null;
  uploadingLogo = false;
  uploadingQrCode = false;
  uploading = false;
  currentSettings: any;

  constructor(
    private fb: FormBuilder,
    private settingsService: SettingsService,
    private router: Router
  ) {
    this.settingsForm = this.fb.group({
      businessName: ['', Validators.required],
      contactNumber: ['', Validators.required],
      logoPath: [''],
      qrCodePath: ['']
    });
  }

  ngOnInit(): void {
    this.loadSettings();
  }

  loadSettings(): void {
    this.settingsService.getSettings().subscribe(settings => {
      this.currentSettings = settings;
      this.settingsForm.patchValue(settings);
      if (settings.logoPath) {
        this.logoPreview = settings.logoPath;
      }
      if (settings.qrCodePath) {
        this.qrCodePreview = settings.qrCodePath;
      }
    });
  }

  onLogoSelected(event: any): void {
    const file: File = event.target.files[0];
    if (file) {
      this.uploadingLogo = true;
      const reader = new FileReader();
      reader.readAsDataURL(file);
      reader.onload = () => {
        this.logoPreview = reader.result;
        this.uploadImage(file, 'logo').pipe(
          finalize(() => this.uploadingLogo = false)
        ).subscribe(response => {
          this.settingsForm.patchValue({ logoPath: response.path });
        });
      };
    }
  }

  onQrCodeSelected(event: any): void {
    const file: File = event.target.files[0];
    if (file) {
      this.uploadingQrCode = true;
      const reader = new FileReader();
      reader.readAsDataURL(file);
      reader.onload = () => {
        this.qrCodePreview = reader.result;
        this.uploadImage(file, 'qrcode').pipe(
          finalize(() => this.uploadingQrCode = false)
        ).subscribe(response => {
          this.settingsForm.patchValue({ qrCodePath: response.path });
        });
      };
    }
  }

  uploadImage(file: File, type: 'logo' | 'qrcode') {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('type', type);
    return this.settingsService.uploadImage(formData);
  }

  onSubmit(): void {
    if (this.settingsForm.valid) {
      this.uploading = true;
      this.settingsService.updateSettings(this.settingsForm.value)
        .pipe(finalize(() => this.uploading = false))
        .subscribe({
          next: () => alert('Settings saved successfully!'),
          error: (err) => alert('Error saving settings: ' + err.message)
        });
    }
  }
}