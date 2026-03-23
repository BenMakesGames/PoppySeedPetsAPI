import { Component, computed, input } from '@angular/core';
import { PetBadgeImgSrcPipe } from "../pipe/pet-badge-img-src.pipe";
import { PetBadgeNamePipe } from "../pipe/pet-badge-name.pipe";
import { DatePipe } from "@angular/common";

@Component({
    selector: 'app-pet-badge-table',
    imports: [
        PetBadgeImgSrcPipe,
        PetBadgeNamePipe,
        DatePipe
    ],
    templateUrl: './pet-badge-table.component.html',
    styleUrl: './pet-badge-table.component.scss'
})
export class PetBadgeTableComponent {
  badges = input.required<PetBadgeInterface[]>();
  sortedBadges = computed(() => this.badges().sort((a, b) => a.dateAcquired.localeCompare(b.dateAcquired)));
}

export interface PetBadgeInterface
{
  badge: string;
  dateAcquired: string;
}