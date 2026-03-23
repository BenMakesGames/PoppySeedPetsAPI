import {Component, OnDestroy, OnInit} from '@angular/core';
import {ApiService} from "../../../shared/service/api.service";
import {GuildEncyclopediaSerializationGroup} from "../../../../model/encyclopedia/guild-encyclopedia.serialization-group";
import {Subscription} from "rxjs";

@Component({
    selector: 'app-guild-directory',
    templateUrl: './guild-directory.component.html',
    styleUrls: ['./guild-directory.component.scss'],
    standalone: false
})
export class GuildDirectoryComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Poppyopedia - Guilds' };

  guilds: GuildEncyclopediaSerializationGroup[];

  guildsAjax: Subscription;

  constructor(private api: ApiService) { }

  ngOnInit(): void {
    this.guildsAjax = this.api.get<GuildEncyclopediaSerializationGroup[]>('/guild').subscribe({
      next: r => {
        this.guilds = r.data;
      }
    })
  }

  ngOnDestroy(): void {
    this.guildsAjax.unsubscribe();
  }

}
