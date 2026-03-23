import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { LoadingThrobberComponent } from "../loading-throbber/loading-throbber.component";
import { CommonModule } from "@angular/common";
import { FormsModule } from "@angular/forms";
import { ApiService } from "../../service/api.service";
import { Subscription } from 'rxjs';
import { FilterResultsSerializationGroup } from "../../../../model/filter-results.serialization-group";

@Component({
    selector: 'app-select-merit',
    templateUrl: './select-merit.component.html',
    imports: [
        CommonModule,
        FormsModule,
        LoadingThrobberComponent,
    ],
    styleUrls: ['./select-merit.component.scss']
})
export class SelectMeritComponent implements OnInit {

  static meritSubscription = Subscription.EMPTY;
  static cachedMerits: MeritModel[]|null = null;

  merits: MeritModel[]|null = null;

  constructor(private api: ApiService) {
  }

  ngOnInit(): void {
    this.loadMerits();
  }

  private loadMerits()
  {
    if(SelectMeritComponent.cachedMerits !== null)
    {
      this.merits = SelectMeritComponent.cachedMerits;
      return;
    }

    if(!SelectMeritComponent.meritSubscription.closed)
      return;

    SelectMeritComponent.meritSubscription = this.api.get<FilterResultsSerializationGroup<MeritModel>>('/encyclopedia/merit', { pageSize: 200 }).subscribe({
      next: response => {
        SelectMeritComponent.cachedMerits = response.data.results.sort((a, b) => a.name.localeCompare(b.name));

        this.merits = SelectMeritComponent.cachedMerits;
      }
    });
  }

  @Input() anyLabel: string|null = 'Any';

  @Input() value: number|null = null;
  @Output() valueChange = new EventEmitter<number|null>();

}

interface MeritModel
{
  id: number;
  name: string;
  description: string;
}