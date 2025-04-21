import { Component, OnInit } from '@angular/core';
import {StoryModel} from "../../adventure-editor/model/story.model";
import {StorySectionModel} from "../../adventure-editor/model/story-section.model";
import { NgIf, NgFor } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { VnChoicesEditorComponent } from '../component/vn-choices-editor/vn-choices-editor.component';
import { VnGraphComponent } from '../component/vn-graph/vn-graph.component';

@Component({
  selector: 'app-page',
  templateUrl: './vn-editor.component.html',
  styleUrls: ['./vn-editor.component.scss'],
  standalone: true,
  imports: [NgIf, FormsModule, NgFor, VnChoicesEditorComponent, VnGraphComponent]
})
export class VnEditorComponent implements OnInit {

  readonly SECTION_STYLES = [
    'dialog', 'description', 'fullFramePicture'
  ];

  fileReader: FileReader;

  stories: StoryModel[];
  storySections: StorySectionModel[];

  selectedStoryId: number|null;
  selectedSections: StorySectionModel[];

  expandedSectionId;

  constructor() { }

  ngOnInit() {
    this.fileReader = new FileReader();

    this.fileReader.onloadend = () => {
      const text = <string>this.fileReader.result;
      const data = JSON.parse(text);

      this.stories = data.find(o => o.type === 'table' && o.name === 'story').data.map(d => {
        return <StoryModel>{
          id: parseInt(d.id),
          first_section_id: parseInt(d.firstChange),
          title: d.title
        }
      });

      this.storySections = data.find(o => o.type === 'table' && o.name === 'story_section').data.map(d => {
        return <StorySectionModel>{
          id: parseInt(d.id),
          story_id: parseInt(d.story_id),
          style: d.style,
          background: d.background,
          image: d.image,
          content: d.content,
          choices: JSON.parse(d.choices)
        };
      });

      if(this.stories.length > 0)
        this.doSelectStory(this.stories[0].id);

      console.log(data);
    };
  }

  doExpandCollapse(sectionId)
  {
    if(sectionId == this.expandedSectionId)
      this.expandedSectionId = null;
    else
      this.expandedSectionId = sectionId;
  }

  doAddStory()
  {
    /*this.stories.push({

    });*/
  }

  doSelectStory(storyId: number)
  {
    this.selectedStoryId = storyId;
    this.selectedSections = this.storySections.filter(ss => ss.story_id == storyId);
  }

  doAddSection()
  {
    const largestId = Math.max(...this.storySections.map(ss => ss.id));

    this.storySections.push({
      id: largestId + 1,
      story_id: this.selectedStoryId,
      style: this.SECTION_STYLES[0],
      background: null,
      image: null,
      content: '',
      choices: []
    });

    this.doSelectStory(this.selectedStoryId);
  }

  doDeleteSection(sectionId)
  {
    this.storySections = this.storySections.filter(ss => ss.id != sectionId);

    if(this.expandedSectionId == sectionId)
      this.expandedSectionId = null;

    this.doSelectStory(this.selectedStoryId);
  }

  doNoFile()
  {
    this.stories = [];
    this.storySections = [];
  }

  doSelectFile(event) {
    const file: File = event.target.files[0];

    this.fileReader.readAsText(file);
  }

}
