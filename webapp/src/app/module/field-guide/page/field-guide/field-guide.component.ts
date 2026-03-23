import { Component, OnInit } from '@angular/core';
import { Subscription } from "rxjs";
import { ApiService } from "../../../shared/service/api.service";
import { FieldGuideEntry } from "../../model/field-guide-entry.serialization-group";
import { ActivatedRoute } from "@angular/router";

@Component({
    selector: 'app-field-guide',
    templateUrl: './field-guide.component.html',
    styleUrls: ['./field-guide.component.scss'],
    standalone: false
})
export class FieldGuideComponent implements OnInit {
  pageMeta = { title: 'Field Guide' };

  fieldGuideSubscription = Subscription.EMPTY;
  fieldGuide: FieldGuideEntry[]|null = null;
  entryIndex = -1;
  location = 'cover';

  constructor(
    private api: ApiService, private activatedRoute: ActivatedRoute
  ) { }

  ngOnInit(): void {
    this.loadEntries();

    this.activatedRoute.queryParams.subscribe(params => {
      window.scroll(0, 0);
      const page = (params['page'] ?? '').trim().toLowerCase();

      if(page == 'cover' || page == 'toc' || page.startsWith('entry-'))
        this.location = page;
      else
        this.location = 'cover';

      if(this.location.startsWith('entry-'))
      {
        let index = parseInt(this.location.substring(6));

        if(isNaN(index) || index < 0 || (this.fieldGuide && index > this.fieldGuide.length - 1))
          index = 0;

        this.entryIndex = index;
      }
      else
        this.entryIndex = -1;
    });
  }

  loadEntries()
  {
    this.fieldGuideSubscription.unsubscribe();

    this.fieldGuideSubscription = this.api.get<FieldGuideEntry[]>('/fieldGuide').subscribe({
      next: r => {
        this.fieldGuide = r.data;
        if(this.entryIndex > this.fieldGuide.length - 1)
          this.entryIndex = 0;
      }
    });
  }
}
