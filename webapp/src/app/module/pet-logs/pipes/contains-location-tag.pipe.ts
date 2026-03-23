import { Pipe, PipeTransform } from '@angular/core';
import { ActivityLogTagSerializationGroup } from "../../../model/activity-log-tag.serialization-group";
import { ReenactmentDialog } from "../dialogs/reenactment/reenactment.dialog";

@Pipe({
  name: 'containsLocationTag',
  standalone: true
})
export class ContainsLocationTagPipe implements PipeTransform {

  transform(tags: ActivityLogTagSerializationGroup[]): unknown {
    return tags.some(t => t.title in ReenactmentDialog.locationPictures);
  }

}
