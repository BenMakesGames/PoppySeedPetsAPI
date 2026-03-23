import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import {GreenhouseComponent} from "./page/greenhouse/greenhouse.component";
import {GreenhouseRoutingModule} from "./greenhouse-routing.module";
import { PlantPollinatorComponent } from './components/plant-pollinator/plant-pollinator.component';
import { ProgressBarComponent } from "../shared/component/progress-bar/progress-bar.component";
import { PetAppearanceComponent } from "../shared/component/pet-appearance/pet-appearance.component";
import { LoadingThrobberComponent } from "../shared/component/loading-throbber/loading-throbber.component";

@NgModule({
  declarations: [
    GreenhouseComponent,
    PlantPollinatorComponent,
  ],
  imports: [
    CommonModule,
    GreenhouseRoutingModule,
    ProgressBarComponent,
    PetAppearanceComponent,
    LoadingThrobberComponent,
  ]
})
export class GreenhouseModule { }
