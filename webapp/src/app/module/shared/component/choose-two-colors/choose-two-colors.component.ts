import {Component, EventEmitter, Input, Output} from '@angular/core';
import { ColorPickerDirective } from "ngx-color-picker";

@Component({
    selector: 'app-choose-two-colors',
    templateUrl: './choose-two-colors.component.html',
    imports: [
        ColorPickerDirective
    ],
    styleUrls: ['./choose-two-colors.component.scss']
})
export class ChooseTwoColorsComponent {

  @Input() colorA: string;
  @Output() colorAChange = new EventEmitter<string>();

  @Input() colorB: string;
  @Output() colorBChange = new EventEmitter<string>();

  doSwapColors()
  {
    const temp = this.colorA;

    this.doChangeColorA(this.colorB);
    this.doChangeColorB(temp);
  }

  doColorPickerA($event)
  {
    this.doChangeColorA($event.substr(1));
  }

  doColorPickerB($event)
  {
    this.doChangeColorB($event.substr(1));
  }

  doChangeColorA(color)
  {
    this.colorA = color;
    this.colorAChange.emit(this.colorA);
  }

  doChangeColorB(color)
  {
    this.colorB = color;
    this.colorBChange.emit(this.colorB);
  }

  doRandomizeColors()
  {
    const r1 = Math.floor(Math.random() * 256);
    const g1 = Math.floor(Math.random() * 256);
    const b1 = Math.floor(Math.random() * 256);
    const r2 = Math.floor(Math.random() * 256);
    const g2 = Math.floor(Math.random() * 256);
    const b2 = Math.floor(Math.random() * 256);

    this.doChangeColorA(this.toHex(r1) + this.toHex(g1) + this.toHex(b1));
    this.doChangeColorB(this.toHex(r2) + this.toHex(g2) + this.toHex(b2));
  }

  toHex(c): string
  {
    return ('00' + c.toString(16)).slice(-2);
  }

}
