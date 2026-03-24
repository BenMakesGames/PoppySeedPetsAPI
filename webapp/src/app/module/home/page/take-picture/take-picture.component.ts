import { Component, ElementRef, OnInit, ViewChild } from '@angular/core';
import {ActivatedRoute} from "@angular/router";
import { MyPetSerializationGroup } from "../../../../model/my-pet/my-pet.serialization-group";
import { domToPng } from 'modern-screenshot';
import download from 'downloadjs';

@Component({
    templateUrl: './take-picture.component.html',
    styleUrls: ['./take-picture.component.scss'],
    standalone: false
})
export class TakePictureComponent implements OnInit {

  cameraId: number;
  draggedSubject: DraggedSubject|null = null;
  selectedSubject: SubjectDto|null = null;

  backgrounds = [
    {
      url: 'beach',
      description: 'the beach, shaded by palms; a seagull is walking by'
    },
    {
      url: 'volcano',
      description: 'on a hill near a river overlooking a volcano'
    },
    {
      url: 'umbra',
      description: 'the Umbra, with dead trees, and ruined buildings'
    },
    {
      url: 'living-room',
      description: 'a small living room with a couch and TV',
    },
    {
      url: 'jungle-ruins',
      description: 'overgrown ruins in the jungle',
    },
    {
      url: 'the-portal',
      description: 'a lab with an experimental portal to another dimension',
    },
    {
      url: 'abandoned-quarry',
      description: 'an abandoned quarry',
    },
    {
      url: 'hollow-log',
      description: 'a large hollow log in the jungle; red eyes are peering out from inside the log'
    },
    {
      url: 'neighborhood',
      description: 'some houses in a neighborhood',
    },
    {
      url: 'lake',
      description: 'a large lake in the jungle; a cliff face on the left'
    },
    {
      url: 'stream',
      description: 'a stream in the jungle',
    },
    {
      url: 'micro-jungle',
      description: 'a bright day in the jungle, with a fruit-bearing tropical tree'
    },
    {
      url: 'icy-moon',
      description: 'the surface of an icy moon',
    },
    {
      url: 'noetala',
      description: 'a misty green cave filled with curved stalagmites and stalactites, and clusters of red eyes',
    }
  ];

  subjects: SubjectDto[] = [];
  background: { url: string, description: string };

  demoCaptions = [
    "Casual chaos",
    "Technically behaving",
    "Shenanigans!!",
    "Caffeinated and slightly unsupervised",
    "In our defense, it was a full moon",
    "For science!",
    "1... 2... 3... say \"fuzzy pickles!\"",
  ];
  caption: string;

  downloading = false;

  @ViewChild('scene') scene: ElementRef;
  @ViewChild('polaroid') polaroid: ElementRef;

  constructor(private activatedRoute: ActivatedRoute)
  {
    this.background = this.backgrounds[Math.floor(Math.random() * this.backgrounds.length)];
    this.caption = this.demoCaptions[Math.floor(Math.random() * this.demoCaptions.length)];
  }

  ngOnInit()
  {
    this.cameraId = parseInt(this.activatedRoute.snapshot.paramMap.get('id'));
  }

  doMoveBack()
  {
    if(this.selectedSubject.index == 0) return; // already at the back

    // swap with the previous subject
    const previousSubject = this.subjects[this.selectedSubject.index - 1];
    this.subjects[this.selectedSubject.index - 1] = this.subjects[this.selectedSubject.index];
    this.subjects[this.selectedSubject.index] = previousSubject;

    // update indicies
    this.selectedSubject.index--;
    previousSubject.index++;
  }

  doMoveForward()
  {
    if(this.selectedSubject.index == this.subjects.length - 1) return; // already at the front

    // swap with the next subject
    const nextSubject = this.subjects[this.selectedSubject.index + 1];
    this.subjects[this.selectedSubject.index + 1] = this.subjects[this.selectedSubject.index];
    this.subjects[this.selectedSubject.index] = nextSubject;

    // update indicies
    this.selectedSubject.index++;
    nextSubject.index--;
  }

  doRemoveSelectedSubject()
  {
    this.subjects.splice(this.selectedSubject.index, 1);
    this.selectedSubject = null;
  }

  doAddSubject(pet: MyPetSerializationGroup)
  {
    if(!pet)
      return;

    const existingSubjectIndex = this.subjects.findIndex(p => p.pet.id == pet.id);

    if(existingSubjectIndex >= 0)
    {
      if(this.subjects[existingSubjectIndex].pet.id == this.selectedSubject.pet.id)
        this.selectedSubject = null;

      this.subjects.splice(existingSubjectIndex, 1);
    }
    else
    {
      const newSubject = {
        index: this.subjects.length,
        pet: pet,
        petHasMoonbound: pet.merits.some(m => m.name === 'Moon-bound' ),
        flipX: false,
        showEgg: true,
        showAura: true,
        showTool: true,
        showHat: true,
        showMoonbound: false,
        showSpiritCompanion: true,
        showLunchbox: pet.lunchboxItems.length > 0,
        x: 0.5,
        y: 0.5,
        sizeInInches: 1,
        angle: 0
      };

      this.selectedSubject = newSubject;

      this.subjects.push(newSubject);
    }
  }

  doStartDragging(subject: SubjectDto, screenX, screenY)
  {
    this.selectedSubject = subject;
    this.draggedSubject = {
      subject: subject,
      startPixelX: screenX,
      startPixelY: screenY,
      subjectStartX: subject.x,
      subjectStartY: subject.y
    };
  }

  doContinueDraggingMouse(event: MouseEvent)
  {
    event.preventDefault();

    if(!this.draggedSubject) return;

    const deltaX = event.screenX - this.draggedSubject.startPixelX;
    const deltaY = event.screenY - this.draggedSubject.startPixelY;

    // get scene size:
    const sceneWidth = this.scene.nativeElement.offsetWidth;
    const sceneHeight = this.scene.nativeElement.offsetHeight;

    this.draggedSubject.subject.x = this.draggedSubject.subjectStartX + deltaX / sceneWidth;
    this.draggedSubject.subject.y = this.draggedSubject.subjectStartY + deltaY / sceneHeight;
  }

  doContinueDraggingTouch(touch: TouchEvent)
  {
    if(!this.draggedSubject) return;

    touch.preventDefault();

    const deltaX = touch.touches[0].screenX - this.draggedSubject.startPixelX;
    const deltaY = touch.touches[0].screenY - this.draggedSubject.startPixelY;

    // get scene size:
    const sceneWidth = this.scene.nativeElement.offsetWidth;
    const sceneHeight = this.scene.nativeElement.offsetHeight;

    this.draggedSubject.subject.x = this.draggedSubject.subjectStartX + deltaX / sceneWidth;
    this.draggedSubject.subject.y = this.draggedSubject.subjectStartY + deltaY / sceneHeight;
  }

  doStopDragging(event: Event)
  {
    if(!this.draggedSubject) return;

    event.preventDefault();

    this.draggedSubject = null;
  }

  doDownload() {
    if(this.downloading) return;

    this.downloading = true;

    domToPng(this.polaroid.nativeElement).then(dataUrl => {
      download(dataUrl, this.caption.replace(/[^a-zA-Z0-9() !_'".+$\[\]=]/g, '-') + '.png');
      this.downloading = false;
    }).catch(() => {
      this.downloading = false;
    });
  }
}

interface DraggedSubject
{
  subject: SubjectDto;
  startPixelX: number;
  startPixelY: number;
  subjectStartX: number;
  subjectStartY: number;
}

interface SubjectDto
{
  index: number;
  pet: MyPetSerializationGroup;
  petHasMoonbound: boolean;
  flipX: boolean;
  showAura: boolean;
  showEgg: boolean;
  showTool: boolean;
  showHat: boolean;
  showMoonbound: boolean;
  showSpiritCompanion: boolean;
  showLunchbox: boolean;
  x: number;
  y: number;
  sizeInInches: number;
  angle: number;
}
