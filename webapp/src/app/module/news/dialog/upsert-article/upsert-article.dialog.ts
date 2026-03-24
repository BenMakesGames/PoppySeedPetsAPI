import {Component, EventEmitter, Inject, Output} from '@angular/core';
import { ArticleAdminSerializationGroup } from "../../../../model/admin/article-admin.serialization-group";
import { ApiService } from "../../../shared/service/api.service";
import { AreYouSureDialog } from "../../../../dialog/are-you-sure/are-you-sure.dialog";
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";

@Component({
    templateUrl: './upsert-article.dialog.html',
    styleUrls: ['./upsert-article.dialog.scss'],
    standalone: false
})
export class UpsertArticleDialog {

  @Output() reloadArticles = new EventEmitter();

  designGoals: { name: string, id: number }[] = [];
  article: ArticleAdminSerializationGroup;
  saving = false;
  articleDesignGoalIds: number[] = [];

  constructor(
    private dialogRef: MatDialogRef<UpsertArticleDialog>,
    @Inject(MAT_DIALOG_DATA) private data: any,
    private api: ApiService,
    private matDialog: MatDialog,
  ) {
    if(data.article)
      this.article = data.article;
    else
    {
      this.article = {
        id: null,
        imageUrl: null,
        title: '',
        body: '',
        designGoals: [],
      };
    }

    this.articleDesignGoalIds = this.article.designGoals.map(dg => dg.id);

    this.api.get<{ name: string, id: number }[]>('/designGoal').subscribe({
      next: r => {
        this.designGoals = r.data;
      }
    });
  }

  doClose()
  {
    AreYouSureDialog.open(this.matDialog, 'Really?', 'You know I have to ask. Just in case.', 'Yep!', 'No, no, no!')
      .afterClosed()
      .subscribe({
        next: (confirmed: boolean) => {
          if(confirmed)
            this.dialogRef.close();
        }
      })
    ;

  }

  doToggleDesignGoal(designGoal: { name: string, id: number })
  {
    if(this.article.designGoals.some(dg => dg.id === designGoal.id))
      this.article.designGoals = this.article.designGoals.filter(dg => dg.id !== designGoal.id);
    else
      this.article.designGoals.push(designGoal);

    this.articleDesignGoalIds = this.article.designGoals.map(dg => dg.id);
  }

  doSave()
  {
    if(this.saving) return;

    this.saving = true;

    let url = '/article';

    if(this.article.id) url += '/' + this.article.id;

    const articleData = {
      id: this.article.id,
      imageUrl: this.article.imageUrl,
      title: this.article.title,
      body: this.article.body,
      designGoals: this.articleDesignGoalIds
    };

    this.api.post(url, articleData).subscribe({
      next: () => {
        this.saving = false;
        this.reloadArticles.emit();
        this.dialogRef.close();
      },
      error: () => {
        this.saving = false;
      }
    });
  }

  public static open(matDialog: MatDialog, article: ArticleAdminSerializationGroup): MatDialogRef<UpsertArticleDialog>
  {
    return matDialog.open(UpsertArticleDialog, {
      disableClose: true,
      minWidth: '50vw',
      data: {
        article: article
      }
    });
  }
}
