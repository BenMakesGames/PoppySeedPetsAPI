import {Component, OnDestroy, OnInit} from '@angular/core';
import {ActivatedRoute} from "@angular/router";
import {ApiService} from "../../../shared/service/api.service";
import {Title} from "@angular/platform-browser";
import {ApiResponseModel} from "../../../../model/api-response.model";
import {GuildMemberSerializationGroup} from "../../../../model/guild/guild-member.serialization-group";
import {GuildEncyclopediaSerializationGroup} from "../../../../model/encyclopedia/guild-encyclopedia.serialization-group";
import {FilterResultsSerializationGroup} from "../../../../model/filter-results.serialization-group";
import {Subscription} from "rxjs";

@Component({
    selector: 'app-guild',
    templateUrl: './guild.component.html',
    styleUrls: ['./guild.component.scss'],
    standalone: false
})
export class GuildComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Poppyopedia - Guild' };

  guild: GuildEncyclopediaSerializationGroup;
  guildMembers: FilterResultsSerializationGroup<GuildMemberSerializationGroup>;

  guildAjax: Subscription;

  constructor(
    private activatedRoute: ActivatedRoute, private api: ApiService,
    private titleService: Title
  ) {

  }

  ngOnDestroy(): void {
    if(this.guildAjax)
      this.guildAjax.unsubscribe();
  }

  doChangePage(page: number)
  {
    if(this.guildAjax && !this.guildAjax.closed)
      this.guildAjax.unsubscribe();

    this.guildAjax = this.api.get<GuildResponseModel>('/guild/' + this.guild.id, { page: page }).subscribe({
      next: (r: ApiResponseModel<GuildResponseModel>) => {
        this.guildMembers = r.data.members;
      },
      error: () => {
      }
    });

  }

  ngOnInit() {
    // no need to unsubscribe from paramMap, apparently
    this.activatedRoute.paramMap.subscribe(params => {
      const guildId = params.get('guild');

      this.guildAjax = this.api.get<GuildResponseModel>('/guild/' + guildId).subscribe({
        next: (r: ApiResponseModel<GuildResponseModel>) => {
          this.guild = r.data.guild;
          this.guildMembers = r.data.members;
          this.titleService.setTitle('Poppy Seed Pets - Poppyopedia - Guild - ' + this.guild.name);
        },
        error: () => {
        }
      });
    });
  }
}

interface GuildResponseModel
{
  guild: GuildEncyclopediaSerializationGroup;
  members: FilterResultsSerializationGroup<GuildMemberSerializationGroup>;
}
