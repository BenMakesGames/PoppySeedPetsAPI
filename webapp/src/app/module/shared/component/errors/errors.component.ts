import {Component, Input} from '@angular/core';
import { CommonModule } from "@angular/common";

@Component({
    selector: 'app-errors',
    templateUrl: './errors.component.html',
    imports: [
        CommonModule
    ],
    styleUrls: ['./errors.component.scss']
})
export class ErrorsComponent {

  @Input() errors: string[];

}
