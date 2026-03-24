import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { environment } from '../../../../../environments/environment';

@Component({
  selector: 'app-pet-logs-links',
  imports: [CommonModule, RouterModule],
  template: `
    <p class="buttons">
      <a [routerLink]="['/poppyopedia/pet/' + petId]" (click)="onClose()">
        {{ hasMultiplePages ? 'View charts & older logs...' : 'View charts...' }}
      </a>
    </p>
  `
})
export class PetLogsLinksComponent {
  @Input() petId: number;
  @Input() hasMultiplePages: boolean;
  @Input() onClose: () => void;

  protected readonly environment = environment;
} 