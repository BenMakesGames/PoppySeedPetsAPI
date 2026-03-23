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
