import { Component, Input, OnInit } from '@angular/core';

@Component({
    selector: 'app-describe-pet-range',
    templateUrl: './describe-pet-range.component.html',
    styleUrls: ['./describe-pet-range.component.scss'],
    standalone: false
})
export class DescribePetRangeComponent implements OnInit {

  @Input() min!: number;
  @Input() max!: number;

  constructor() { }

  ngOnInit(): void {
  }

}
