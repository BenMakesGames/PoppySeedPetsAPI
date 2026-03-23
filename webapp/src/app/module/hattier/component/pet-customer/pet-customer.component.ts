import { Component, EventEmitter, Input, Output } from '@angular/core';
import { MyPetSerializationGroup } from "../../../../model/my-pet/my-pet.serialization-group";

@Component({
    selector: 'app-pet-customer',
    templateUrl: './pet-customer.component.html',
    styleUrls: ['./pet-customer.component.scss'],
    standalone: false
})
export class PetCustomerComponent {

  @Input() pet: MyPetSerializationGroup;
  @Input() buttonText = 'Enter Dressing Room';

  @Output() select = new EventEmitter();

  doSelect()
  {
    this.select.emit();
  }
}
