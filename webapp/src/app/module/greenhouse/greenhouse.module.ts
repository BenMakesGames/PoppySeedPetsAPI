/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
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
