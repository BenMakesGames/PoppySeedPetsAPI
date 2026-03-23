import { Component, EventEmitter, Input, OnChanges, Output, SimpleChanges } from '@angular/core';
import { FormsModule } from "@angular/forms";
import { MatDialog } from "@angular/material/dialog";
import { PetSearchDialog } from "../../dialog/pet-search/pet-search.dialog";
import { CreatePetSearchModel, PetSearchModel } from "../../../../model/search/pet-search-model";
import { FilterAreaComponent } from "../../../filters/components/filter-area/filter-area.component";

@Component({
  imports: [
    FormsModule,
    FilterAreaComponent,
  ],
  selector: 'app-pet-search',
  templateUrl: './pet-search.component.html',
  styleUrls: ['./pet-search.component.scss']
})
export class PetSearchComponent implements OnChanges {

  @Input() filters: PetSearchModel = CreatePetSearchModel();
  @Output() filtersChange = new EventEmitter<PetSearchModel>();

  name: string;

  constructor(private matDialog: MatDialog) { }

  ngOnChanges(changes: SimpleChanges)
  {
    if('filters' in changes)
    {
      this.name = this.filters.name;
    }
  }

  doSearch(filters: PetSearchModel)
  {
    this.filters = filters;
    this.filtersChange.emit(filters);
  }

  doMore()
  {
    PetSearchDialog.open(this.matDialog, this.filters).afterClosed().subscribe({
      next: (r: PetSearchModel) => {
        if(r)
        {
          this.filtersChange.emit(r);
        }
      }
    });
  }
}
