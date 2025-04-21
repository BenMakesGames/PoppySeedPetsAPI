import {Component, EventEmitter, Input, OnInit, Output} from '@angular/core';
import {StorySectionModel} from "../../../adventure-editor/model/story-section.model";
import {StoryChoiceActionSetStepModel} from "../../../adventure-editor/model/story-choice-action-set-step.model";
import {GraphChangesService} from "../../service/graph-changes.service";
import { FormsModule } from '@angular/forms';
import { NgFor, SlicePipe } from '@angular/common';

@Component({
  selector: 'app-vn-set-step-action-editor',
  templateUrl: './vn-set-step-action-editor.component.html',
  styleUrls: ['./vn-set-step-action-editor.component.scss'],
  standalone: true,
  imports: [FormsModule, NgFor, SlicePipe]
})
export class VnSetStepActionEditorComponent implements OnInit {

  @Input() storySections: StorySectionModel[];
  @Input() action: StoryChoiceActionSetStepModel;
  @Output() actionChange = new EventEmitter<StoryChoiceActionSetStepModel>();

  constructor(private graphChanges: GraphChangesService) { }

  ngOnInit(): void {
  }

  doChangeStep()
  {
    this.actionChange.emit(this.action);

    this.graphChanges.changes.next();
  }

}
