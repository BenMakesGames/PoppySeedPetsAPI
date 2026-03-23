import { Component, ElementRef, EventEmitter, Input, OnInit, Output, SimpleChanges, ViewChild } from '@angular/core';
import {fromEvent, Observable, Subscription} from "rxjs";
import {ApiService} from "../../service/api.service";
import {concat, debounceTime, distinctUntilChanged, filter, map, switchMap} from "rxjs/operators";
import {ApiResponseModel} from "../../../../model/api-response.model";
import { LoadingThrobberComponent } from "../loading-throbber/loading-throbber.component";
import { CommonModule, NgOptimizedImage } from "@angular/common";
import { FormsModule } from "@angular/forms";
import { PetSpeciesTypeaheadModel } from "../find-pet-species-by-name/find-pet-species-by-name.component";

@Component({
    selector: 'app-find-item-by-name',
    templateUrl: './find-item-by-name.component.html',
    imports: [
        LoadingThrobberComponent,
        CommonModule,
        NgOptimizedImage,
        FormsModule,
    ],
    styleUrls: ['./find-item-by-name.component.scss']
})
export class FindItemByNameComponent implements OnInit {

  @Input() label: string = 'Item Name (or part thereof)';

  @Input() value: number|null = null;
  @Output() valueChange = new EventEmitter<number|null>();

  @ViewChild('search', { 'static': true }) search: ElementRef;

  keyUpSubscription: Subscription;

  searching = false;
  results: ItemTypeaheadModel[]|null = null;
  selected: ItemTypeaheadModel|null = null;

  constructor(private api: ApiService) {

  }

  ngOnInit() {
    this.keyUpSubscription = fromEvent(this.search.nativeElement, 'keyup')
      .pipe(
        filter((e: KeyboardEvent) => e.keyCode !== 13),
        map((e: any) => e.target.value),
        debounceTime(400),
        concat(),
        distinctUntilChanged(),
        filter(q => q.length > 0),
        switchMap(q => this.suggest(q))
      )
      .subscribe({
        next: (r: ApiResponseModel<ItemTypeaheadModel[]>) => {
          this.results = r.data;
          FindItemByNameComponent.addResultsToCache(this.results);
          this.searching = false;
        },
        error: () => {
          this.searching = false;
        }
      })
    ;
  }

  ngOnChanges(changes: SimpleChanges) {
    if('value' in changes)
    {
      const item = FindItemByNameComponent.itemCache.find(x => x.id === this.value);
      if(item)
      {
        this.selected = item;
        this.search.nativeElement.value = item.name;
      }
    }
  }

  ngOnDestroy()
  {
    this.keyUpSubscription.unsubscribe();
  }

  suggest(search: string): Observable<ApiResponseModel<ItemTypeaheadModel[]>>
  {
    this.results = null;
    this.searching = true;
    return this.api.get<ItemTypeaheadModel[]>('/encyclopedia/typeahead/item', { search: search });
  }

  doSelectFirstResult()
  {
    if(this.results && this.results.length > 0)
      this.doSelect(this.results[0]);
  }

  doClear()
  {
    this.selected = null;
    this.valueChange.emit(null);
    this.search.nativeElement.value = '';
    this.results = null;
  }

  doSelect(result: PetSpeciesTypeaheadModel)
  {
    this.selected = result;
    this.valueChange.emit(result.id);
    this.search.nativeElement.value = '';
    this.results = null;
  }

  static itemCache: ItemTypeaheadModel[] = [];

  static addResultsToCache(results: ItemTypeaheadModel[])
  {
    results.forEach(result => {
      if(!FindItemByNameComponent.itemCache.find(x => x.id === result.id))
        FindItemByNameComponent.itemCache.push(result);
    });
  }
}

export interface ItemTypeaheadModel
{
  id: number;
  name: string;
  image: string;
}
