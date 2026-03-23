import {
  Component,
  input,
  output,
} from '@angular/core';
import { FormsModule } from "@angular/forms";
import { CreatePlayerSearchModel, PlayerSearchModel } from "../../../../model/search/player-search-model";
import { FilterAreaComponent } from "../../../filters/components/filter-area/filter-area.component";

@Component({
  imports: [
    FormsModule,
    FilterAreaComponent,
  ],
  selector: 'app-player-search',
  templateUrl: './player-search.component.html',
  styleUrls: ['./player-search.component.scss']
})
export class PlayerSearchComponent {

  filters = input<PlayerSearchModel>(CreatePlayerSearchModel());
  filtersChange = output<PlayerSearchModel>();

  doSearch(filters: PlayerSearchModel)
  {
    this.filtersChange.emit(filters);
  }
}
