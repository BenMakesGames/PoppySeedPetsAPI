import { Component, input } from '@angular/core';
import { MyAuraSerializationGroup } from "../../../../model/aura/my-aura.serialization-group";

@Component({
  standalone: true,
  selector: 'app-pet-aura',
  templateUrl: './pet-aura.component.html',
  styleUrls: ['./pet-aura.component.scss']
})
export class PetAuraComponent {
  petScale = input<number>(1);
  aura = input.required<AuraInput>();
}

export interface AuraInput extends MyAuraSerializationGroup
{
  id: number;
  name: string;
  image: string;
  size: number;
  centerX: number;
  centerY: number;
  hue: number|null;
}
