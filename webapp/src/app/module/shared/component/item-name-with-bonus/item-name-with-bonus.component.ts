import {Component, Input, OnInit} from '@angular/core';
import { CommonModule } from "@angular/common";

@Component({
    imports: [
        CommonModule
    ],
    selector: 'app-item-name-with-bonus',
    templateUrl: './item-name-with-bonus.component.html',
    styleUrls: ['./item-name-with-bonus.component.scss']
})
export class ItemNameWithBonusComponent implements OnInit {

  @Input() spice: { name: string, isSuffix: boolean };
  @Input() bonus: { name: string, isSuffix: boolean };
  @Input() item: { name: string };

  constructor() { }

  ngOnInit(): void {
  }

}
