/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, OnDestroy, OnInit } from '@angular/core';
import { CommonModule } from "@angular/common";

@Component({
    selector: 'app-current-moon-phase',
    templateUrl: './current-moon-phase.component.html',
    imports: [
        CommonModule
    ],
    styleUrls: ['./current-moon-phase.component.scss']
})
export class CurrentMoonPhaseComponent implements OnInit, OnDestroy {

  moonPhaseInterval: number;
  moonPhase: string;

  constructor() {
    this.moonPhase = CurrentMoonPhaseComponent.getMoonPhase(new Date());
  }

  ngOnInit() {
    this.moonPhaseInterval = window.setInterval(() => {
      this.moonPhase = CurrentMoonPhaseComponent.getMoonPhase(new Date());
    }, 1000 * 60); // Update every minute
  }

  ngOnDestroy() {
    if(this.moonPhaseInterval)
      window.clearInterval(this.moonPhaseInterval);
  }

  static getMoonPhase(date: Date)
  {
    const year = date.getUTCFullYear();
    const month = date.getUTCMonth() + 1;
    const day = date.getUTCDate();
    const yy = year - Math.floor((12 - month) / 10);
    const mm = (month + 9) % 12;
    const k1 = Math.floor(365.25 * (yy + 4712));
    const k2 = Math.floor(30.6 * mm + 0.5);
    const k3 = Math.floor(Math.floor((yy / 100) + 49) * 0.75) - 38;

    let jd = k1 + k2 + day + 59;

    if(jd > 2299160)
      jd -= k3;

    let ip = (jd - 2451550.1) / 29.530588853;
    ip -= Math.floor(ip);
    if(ip < 0)
      ip++;

    const ag = ip * 29.53;

    if(ag < 1.84566)
      return 'new';
    else if(ag < 5.53699)
      return 'waxing-crescent';
    else if(ag < 9.22831)
      return 'first-quarter';
    else if(ag < 12.91963)
      return 'waxing-gibbous';
    else if(ag < 16.61096)
      return 'full';
    else if(ag < 20.30228)
      return 'waning-gibbous';
    else if(ag < 23.99361)
      return 'last-quarter';
    else if(ag < 27.68493)
      return 'waning-crescent';
    else
      return 'new';
  }

}
