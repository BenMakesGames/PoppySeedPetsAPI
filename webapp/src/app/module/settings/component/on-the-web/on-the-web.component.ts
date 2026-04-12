/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {Component, OnDestroy, OnInit} from '@angular/core';
import {ApiService} from "../../../shared/service/api.service";
import {Subscription} from "rxjs";
import { AddLinkDialog } from "../../dialog/add-link/add-link.dialog";
import { MatDialog } from "@angular/material/dialog";
import { MyUserLinkModel } from "../../model/my-user-link.model";
import { UserDataService } from "../../../../service/user-data.service";

@Component({
    selector: 'app-on-the-web',
    templateUrl: './on-the-web.component.html',
    styleUrls: ['./on-the-web.component.scss'],
    standalone: false
})
export class OnTheWebComponent implements OnInit, OnDestroy
{
  links: MyUserLinkModel[];
  searchAjax = Subscription.EMPTY;
  saveAjax = Subscription.EMPTY;
  userId: number;
  alsoSayNet = false;

  constructor(private api: ApiService, private matDialog: MatDialog, private userData: UserDataService) {
    this.userId = userData.user.value.id;
  }

  ngOnInit() {
    this.loadLinks();
  }

  ngOnDestroy(): void {
    this.searchAjax.unsubscribe();
  }

  doAddLink()
  {
    AddLinkDialog.show(this.matDialog).afterClosed().subscribe({
      next: r => {
        if(r.link)
        {
          this.links.push(r.link);
          this.links = this.links.sort((a, b) => a.website > b.website ? 1 : -1);
        }
      }
    });
  }

  doRemoveLink(link)
  {
    if(!this.saveAjax.closed || !this.searchAjax.closed) return;

    this.saveAjax = this.api.del<MyUserLinkModel[]>('/my/interwebs/' + link.id).subscribe({
      next: () => {
        const linkIndex = this.links.indexOf(link);
        this.links.splice(linkIndex, 1);
      }
    });
  }

  private loadLinks()
  {
    if(!this.saveAjax.closed) return;

    this.searchAjax.unsubscribe();

    this.searchAjax = this.api.get<MyUserLinkModel[]>('/my/interwebs').subscribe({
      next: r => {
        this.links = r.data.sort((a, b) => a.website > b.website ? 1 : -1);
        this.alsoSayNet = this.links.length > 0 && Math.random() <= 0.1;
      }
    });
  }

}
