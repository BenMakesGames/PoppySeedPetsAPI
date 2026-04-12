/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Injectable } from '@angular/core';
import {BehaviorSubject} from "rxjs";
import {PetActivitySerializationGroup} from "../model/pet-activity-logs/pet-activity.serialization-group";
import { MatDialog } from "@angular/material/dialog";
import { ReenactmentDialog } from "../module/pet-logs/dialogs/reenactment/reenactment.dialog";

@Injectable({
  providedIn: 'root'
})
export class MessagesService {

  public activity = new BehaviorSubject<PetActivitySerializationGroup[]>([]);

  constructor(private matDialog: MatDialog) { }

  public clearMessages()
  {
    this.activity.next([]);
  }

  public addMessages(newMessages: PetActivitySerializationGroup[])
  {
    let messages = this.activity.getValue();

    let sortedMessages = newMessages
      .sort((a, b) => b.interestingness - a.interestingness);

    const petLogs = sortedMessages.filter(m => m.hasOwnProperty('pet') && m.pet);

    const messageWeCanRender = petLogs
      .filter(m => m.tags.some(t => ReenactmentDialog.locationPictures.hasOwnProperty(t.title)));

    if(messageWeCanRender.length > 0)
    {
      ReenactmentDialog.open(
        this.matDialog,
        messageWeCanRender.map(m => {
          return {
            pet: m.pet,
            tool: m.equippedItem,
            tags: m.tags,
            createdItems: m.createdItems,
            caption: m.entry
          };
        }),
        petLogs.length - messageWeCanRender.length
      );

      sortedMessages = sortedMessages.filter(m => !petLogs.includes(m));
    }

    if(sortedMessages.length > 5)
      sortedMessages = sortedMessages.slice(0, 5);

    messages.unshift(...sortedMessages);

    if(messages.length > 5)
      messages = messages.slice(0, 5);

    this.activity.next(messages);
  }

  public addGenericMessage(message: string)
  {
    const newMessage = {
      entry: message,
      icon: '',
      createdOn: '',
      interestingness: 9999999,
      tags: [],
      createdItems: [],
    };

    this.addMessages([ newMessage ]);
  }
}
