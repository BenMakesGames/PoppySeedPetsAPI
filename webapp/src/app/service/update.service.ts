import { ApplicationRef, inject, Injectable } from '@angular/core';
import { SwUpdate } from "@angular/service-worker";
import {AskToRestartDialog} from "../dialog/ask-to-restart/ask-to-restart.dialog";
import {concat, interval} from 'rxjs';
import {first} from 'rxjs/operators';
import { MatDialog } from "@angular/material/dialog";

@Injectable({
  providedIn: 'root'
})
export class UpdateService
{
  private appRef = inject(ApplicationRef);
  private updates = inject(SwUpdate);

  constructor(private matDialog: MatDialog)
  {
    const appIsStable = this.appRef.isStable.pipe(first((isStable) => isStable === true));
    const everySixHours = interval(6 * 60 * 60 * 1000);
    const everySixHoursOnceAppIsStable = concat(appIsStable, everySixHours);

    everySixHoursOnceAppIsStable.subscribe(async () => {
      try
      {
        const updateFound = await this.updates.checkForUpdate();

        if(updateFound)
          AskToRestartDialog.open(this.matDialog);
      }
      catch (err)
      {
        console.error('Failed to check for updates:', err);
      }
    });
  }
}
