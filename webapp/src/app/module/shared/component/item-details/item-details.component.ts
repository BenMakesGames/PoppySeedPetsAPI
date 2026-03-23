import { Component, EventEmitter, Input, OnDestroy, OnInit, Output } from '@angular/core';
import {ItemEncyclopediaSerializationGroup} from "../../../../model/encyclopedia/item-encyclopedia.serialization-group";
import {Subscription, timer} from "rxjs";
import { CommonModule } from "@angular/common";
import { RouterLink } from "@angular/router";
import { MarkdownModule } from "ngx-markdown";
import { ItemOtherPropertiesIcons } from "../../../../model/item-other-properties-icons";
import { ItemTagsComponent } from "../item-tags/item-tags.component";

@Component({
    selector: 'app-item-details',
    templateUrl: './item-details.component.html',
    imports: [
        CommonModule,
        RouterLink,
        MarkdownModule,
        ItemTagsComponent
    ],
    styleUrls: ['./item-details.component.scss']
})
export class ItemDetailsComponent implements OnInit, OnDestroy {

  public readonly DOUBLE_QUOTE = '"';

  timerSubscription: Subscription;
  isOctober = false;

  @Input() item: ItemEncyclopediaSerializationGroup;

  @Output() clickedLink = new EventEmitter<void>();

  aSnack = Math.random() <= 0.01;
  readonly itemOtherPropertiesIcons = ItemOtherPropertiesIcons;

  constructor() { }

  ngOnInit() {
    this.timerSubscription = timer(0, 15000).subscribe({
      next: () => {
        this.isOctober = (new Date()).getUTCMonth() === 9;
      }
    })
  }

  ngOnDestroy(): void {
    this.timerSubscription.unsubscribe();
  }

  doClickLink()
  {
    this.clickedLink.emit();
  }
}
