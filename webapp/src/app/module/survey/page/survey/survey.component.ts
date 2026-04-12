/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from "@angular/router";
import { ApiService } from "../../../shared/service/api.service";
import { MessagesService } from "../../../../service/messages.service";

@Component({
    templateUrl: './survey.component.html',
    styleUrls: ['./survey.component.scss'],
    standalone: false
})
export class SurveyComponent implements OnInit {

  saving = false;
  guid = '';
  survey: Survey;
  loading = true;
  answers: { [key:number]:string } = {};
  questions: Question[] = [];

  constructor(
    private activatedRoute: ActivatedRoute, private api: ApiService,
    private messages: MessagesService
  ) { }

  ngOnInit() {
    // no need to unsubscribe from paramMap, apparently
    this.activatedRoute.paramMap.subscribe(params => {
      this.guid = params.get('guid');

      this.api.get<{survey: Survey, questions: Question[], answers: Answer[]}>('/survey/' + this.guid).subscribe({
        next: r => {
          this.survey = r.data.survey;
          this.questions = r.data.questions;
          this.answers = {};

          r.data.answers.forEach(a => {
            this.answers[a.question.id] = a.answer;
          });

          this.loading = false;
        },
        error: () => {
          this.loading = false;
        }
      });
    });
  }

  doSubmit()
  {
    if(this.saving)
      return;

    this.saving = true;

    this.api.post('/survey/' + this.guid, this.answers).subscribe({
      next: () => {
        this.messages.addGenericMessage('Saved! (And if you want to change any of your answers, you can come back any time!)');
        this.saving = false;
      },
      error: () => {
        this.saving = false;
      }
    });
  }
}

interface Survey
{
  title: string,
  description: string,
  endDate: string,
}

interface Question
{
  id: number,
  title: string,
  type: string,
}

interface Answer
{
  question: {
    id: number
  },
  answer: string,
}