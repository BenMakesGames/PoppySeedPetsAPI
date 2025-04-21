import {Component, EventEmitter, Input, OnInit, Output} from '@angular/core';
import {StorySectionModel} from "../../../adventure-editor/model/story-section.model";
import {StoryChoiceModel} from "../../../adventure-editor/model/story-choice.model";
import { NgFor } from '@angular/common';
import { VnSingleChoiceEditorComponent } from '../vn-single-choice-editor/vn-single-choice-editor.component';

@Component({
  selector: 'app-vn-choices-editor',
  templateUrl: './vn-choices-editor.component.html',
  styleUrls: ['./vn-choices-editor.component.scss'],
  standalone: true,
  imports: [NgFor, VnSingleChoiceEditorComponent]
})
export class VnChoicesEditorComponent implements OnInit {

  @Input() storySections: StorySectionModel[];

  @Input() choices: StoryChoiceModel[];
  @Output() choicesOutput = new EventEmitter<StoryChoiceModel[]>();

  constructor() { }

  ngOnInit() {
  }

  doDeleteChoice(choice)
  {
    this.choices = this.choices.filter(c => c !== choice);
  }

  doAddChoice()
  {
    this.choices.push({
      text: '',
      actions: [
        { type: 'exit' }
      ]
    });
  }
}
