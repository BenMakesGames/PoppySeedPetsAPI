/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, OnInit } from '@angular/core';

@Component({
    selector: 'app-not-found',
    templateUrl: './not-found.component.html',
    styleUrls: ['./not-found.component.scss'],
    standalone: false
})
export class NotFoundComponent implements OnInit {

  private static readonly npcList = [
    { npc: 'bells', name: 'Bells' },
    { npc: 'rue', name: 'Rue' },
    { npc: 'museum-curator', name: 'Ridley' },
    { npc: 'pet-shelter-kid', name: '???' },
    { npc: 'mia', name: 'Mia' },
    { npc: 'ant-queen', name: 'Ant Queen' },
  ];

  npc: string;
  name: string;

  pageMeta = { title: '404' };

  constructor() { }

  ngOnInit() {
    const dayOfTheYear = new Date().getDay() + new Date().getFullYear() * 366;
    const npc = NotFoundComponent.npcList[dayOfTheYear % NotFoundComponent.npcList.length];
    this.npc = npc.npc;
    this.name = npc.name;
  }
}
