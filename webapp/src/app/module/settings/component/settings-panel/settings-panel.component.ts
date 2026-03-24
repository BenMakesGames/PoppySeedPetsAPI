import {Component, Input, OnInit} from '@angular/core';

@Component({
    selector: 'app-settings-panel',
    templateUrl: './settings-panel.component.html',
    styleUrls: ['./settings-panel.component.scss'],
    standalone: false
})
export class SettingsPanelComponent implements OnInit {

  @Input() page: string;

  constructor() { }

  ngOnInit() {
  }

}
