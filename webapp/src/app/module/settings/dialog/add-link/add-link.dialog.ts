import { Component, OnInit } from '@angular/core';
import { MatDialog, MatDialogRef } from "@angular/material/dialog";
import { ApiService } from "../../../shared/service/api.service";
import { MyUserLinkModel } from "../../model/my-user-link.model";
import { SocialLinkComponent } from "../../../encyclopedia/component/social-link/social-link.component";

@Component({
    templateUrl: './add-link.dialog.html',
    styleUrls: ['./add-link.dialog.scss'],
    standalone: false
})
export class AddLinkDialog implements OnInit {

  website: string = 'PSP';
  nameOrId: string = '';
  visibility: string = 'Followed';

  websites = SocialLinkComponent.Websites;
  Object = Object;

  saving = false;

  constructor(
    private dialogRef: MatDialogRef<AddLinkDialog>, private api: ApiService
  ) { }

  ngOnInit(): void {
  }

  doAdd()
  {
    if(this.saving) return;

    this.saving = true;

    const data = { website: this.website, nameOrId: this.nameOrId, visibility: this.visibility };

    this.api.post<MyUserLinkModel>('/my/interwebs', data).subscribe({
      next: r => {
        this.dialogRef.close({ link: r.data });
      },
      error: () => {
        this.saving = false;
      }
    });
  }

  doCancel()
  {
    this.dialogRef.close();
  }

  public static show(matDialog: MatDialog)
  {
    return matDialog.open(AddLinkDialog, {
      width: '360px'
    });
  }
}
