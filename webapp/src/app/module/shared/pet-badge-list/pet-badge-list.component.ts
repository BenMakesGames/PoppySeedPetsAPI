import { Component, computed, input } from '@angular/core';
import { PetBadgeImgSrcPipe } from "../pipe/pet-badge-img-src.pipe";
import { PetBadgeNamePipe } from "../pipe/pet-badge-name.pipe";
import { DatePipe } from "@angular/common";

@Component({
    selector: 'app-pet-badge-list',
    imports: [
        PetBadgeImgSrcPipe,
        PetBadgeNamePipe,
        DatePipe
    ],
    templateUrl: './pet-badge-list.component.html',
    styleUrl: './pet-badge-list.component.scss'
})
export class PetBadgeListComponent {
  badges = input.required<PetBadgeInterface[]>();
  sortedBadges = computed(() => this.badges().sort((a, b) => a.dateAcquired.localeCompare(b.dateAcquired)));
}

export interface PetBadgeInterface
{
  badge: string;
  dateAcquired: string;
}