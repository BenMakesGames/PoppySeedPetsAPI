import {
  Component,
  ElementRef,
  EventEmitter,
  HostListener,
  Input,
  OnDestroy,
  OnInit,
  Output,
  ViewChild
} from '@angular/core';
import { ActivityLogTagSerializationGroup } from "../../../../model/activity-log-tag.serialization-group";
import { fromEvent, Subscription } from "rxjs";
import { distinctUntilChanged, filter, map, switchMap } from "rxjs/operators";
import { UserActivityTagRepositoryService } from "../../../../service/user-activity-tag-repository.service";

@Component({
    selector: 'app-user-activity-tag-input',
    templateUrl: './user-activity-tag-input.component.html',
    styleUrls: ['./user-activity-tag-input.component.scss'],
    standalone: false
})
export class UserActivityTagInputComponent implements OnInit, OnDestroy {
  @HostListener('document:mousedown', ['$event'])
  onGlobalClick(event): void {
    if (this.options && !this.options.nativeElement.contains(event.target) && !this.search.nativeElement.contains(event.target))
      this.suggestions = null;
  }

  @Input() tags: ActivityLogTagSerializationGroup[] = [];
  @Output() tagsChange = new EventEmitter<ActivityLogTagSerializationGroup[]>();

  @ViewChild('search', { 'static': true }) search: ElementRef;
  @ViewChild('options', { 'static': false }) options: ElementRef;

  typingSubscription = Subscription.EMPTY;
  navSubscription = Subscription.EMPTY;
  inputFocusSubscription = Subscription.EMPTY;
  loaded = false;

  suggestions: ActivityLogTagSerializationGroup[]|null = null;
  selectedTag = -1;

  constructor(private activityTagRepository: UserActivityTagRepositoryService) {
  }

  ngOnInit() {
    this.activityTagRepository.getMatchingTags('dummy text').subscribe(_ => {
      this.loaded = true;
    });

    this.inputFocusSubscription = fromEvent(this.search.nativeElement, 'focus').subscribe({
      next: () => {
        this.search.nativeElement.dispatchEvent(new KeyboardEvent('keyup'));
      }
    });

    this.typingSubscription = fromEvent(this.search.nativeElement, 'keyup')
      .pipe(
        filter((e: KeyboardEvent) => e.keyCode !== 13),
        map((e: any) => e.target.value),
        distinctUntilChanged(),
        switchMap(q => this.activityTagRepository.getMatchingTags(q.trim()))
      )
      .subscribe({
        next: (tags) => {
          this.suggestions = tags == null ? null : tags.filter(tag => !this.tags.find(t => t.title === tag.title)).slice(0, 10);
          this.selectedTag = -1;
          if(this.suggestions.length > 0)
            setTimeout(() => { this.selectedTag = 0; }, 0);
        }
      })
    ;

    this.navSubscription = fromEvent(this.search.nativeElement, 'keydown')
      .pipe(
        filter((e: KeyboardEvent) => e.key === 'ArrowDown' || e.key === 'ArrowUp' || e.key === 'Enter' || e.key === 'Escape' || e.key === 'Tab' || e.key === 'Backspace')
      )
      .subscribe({
        next: e => {
          switch(e.key)
          {
            case 'ArrowDown':
              e.preventDefault();
              e.cancelBubble = true;
              this.selectedTag = Math.min(this.selectedTag + 1, this.suggestions.length - 1);
              window.document.getElementById('item' + this.selectedTag).scrollIntoView({ block: 'nearest' });
              break;

            case 'ArrowUp':
              e.preventDefault();
              e.cancelBubble = true;
              this.selectedTag = Math.max(this.selectedTag - 1, 0);
              window.document.getElementById('item' + this.selectedTag).scrollIntoView({ block: 'nearest' });
              break;

            case 'Enter':
              if (this.suggestions && this.suggestions.length > 0)
              {
                e.preventDefault();
                e.cancelBubble = true;

                this.doSelectTag(this.suggestions[this.selectedTag]);
              }
              break;

            case 'Escape':
            case 'Tab':
              this.suggestions = null;
              break;

            case 'Backspace':
              if (this.search.nativeElement.value.length === 0)
              {
                this.suggestions = null;

                if(this.tags.length > 0)
                  this.doRemoveTag(this.tags[this.tags.length - 1]);
              }
              break;
          }
        }
      })
    ;
  }

  ngOnDestroy()
  {
    this.typingSubscription.unsubscribe();
    this.navSubscription.unsubscribe();
    this.inputFocusSubscription.unsubscribe();
  }

  doRemoveTag(tag: ActivityLogTagSerializationGroup)
  {
    this.tags = this.tags.filter(t => t !== tag);
    this.tagsChange.next(this.tags);
    this.search.nativeElement.focus();
  }

  doSelectTag(tag: ActivityLogTagSerializationGroup)
  {
    this.tags.push(tag);
    this.search.nativeElement.value = '';
    this.suggestions = null;
    this.search.nativeElement.focus();
    this.search.nativeElement.dispatchEvent(new KeyboardEvent('keyup'));
  }

}
