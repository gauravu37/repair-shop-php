import { Component } from '@angular/core';
import { CommonModule, NgIf } from '@angular/common';
import { IconDirective } from '@coreui/icons-angular';
import { 
  ContainerComponent, 
  RowComponent, 
  ColComponent, 
  CardGroupComponent, 
  TextColorDirective, 
  CardComponent, 
  CardBodyComponent, 
  InputGroupComponent, 
  InputGroupTextDirective, 
  FormControlDirective, 
  ButtonDirective 
} from '@coreui/angular';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { AuthService } from '../../../services/auth.service';
import { Router } from '@angular/router';
import { HttpClientModule } from '@angular/common/http';

@Component({
    selector: 'app-login',
    templateUrl: './login.component.html',
    styleUrls: ['./login.component.scss'],
    standalone: true,
    imports: [
        CommonModule, // <-- Add this for *ngIf support
        // NgIf,      // <-- Alternative: import NgIf directly
		HttpClientModule,
        ContainerComponent, 
        RowComponent, 
        ColComponent, 
        CardGroupComponent, 
        TextColorDirective, 
        CardComponent, 
        CardBodyComponent,
        InputGroupComponent, 
        InputGroupTextDirective, 
        IconDirective, 
        FormControlDirective, 
        ButtonDirective,
        ReactiveFormsModule
    ],
    providers: [AuthService]
})
export class LoginComponent {
  loginForm: FormGroup;
  loading = false;
  error = '';

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private router: Router
  ) {
    this.loginForm = this.fb.group({
      username: ['', [Validators.required, Validators.email]],
      password: ['', Validators.required]
    });
  }

	ngOnInit(): void {
    if (this.authService.isLoggedIn()) {
      const user = this.authService.getCurrentUser();
      //alert(user?.user_type);
      // Redirect based on user type if needed
      if (user?.user_type === 'admin') {
        //this.router.navigate(['/admin/dashboard']);
      } else {
       // this.router.navigate(['/dashboard']);
      }
    }
  }

  onSubmit() {
    this.error = '';
    this.loading = true;
    
    if (this.loginForm.invalid) {
      this.error = 'Please enter valid credentials';
      this.loading = false;
      return;
    }

    const { username, password } = this.loginForm.value;
    this.authService.login(username, password).subscribe({
      next: () => {
        this.loading = false;
        this.router.navigate(['/dashboard']);
      },
      error: (err: any) => {
        this.error = err.error?.message || 'Login failed';
        this.loading = false;
      }
    });
  }
}