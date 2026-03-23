import { Component, Inject, OnInit } from '@angular/core';
import { ApiService } from "../../../shared/service/api.service";
import { MyPetSerializationGroup } from "../../../../model/my-pet/my-pet.serialization-group";
import { FilterResultsSerializationGroup } from "../../../../model/filter-results.serialization-group";
import { UserDataService } from "../../../../service/user-data.service";
import { Subscription } from "rxjs";
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";

@Component({
    templateUrl: './assemble-team.dialog.html',
    styleUrls: ['./assemble-team.dialog.scss'],
    standalone: false
})
export class AssembleTeamDialog implements OnInit {

  page = 0;
  availablePets: FilterResultsSerializationGroup<MyPetSerializationGroup>|null = null;
  selectedPets: MyPetSerializationGroup[] = [];
  requirementsAreMet = false;
  embarking = false;

  step: StepDto;

  loadingAvailablePets = Subscription.EMPTY;

  constructor(
    private dialogRef: MatDialogRef<AssembleTeamDialog>,
    private api: ApiService,
    private userService: UserDataService,
    @Inject(MAT_DIALOG_DATA) private data,
  ) {
    this.step = data.step;
  }

  ngOnInit(): void {
    this.loadAvailablePets();
  }

  private loadAvailablePets()
  {
    const data = {
      page: this.page,
      filter: { owner: this.userService.user.value.id }
    };

    this.loadingAvailablePets.unsubscribe();

    this.loadingAvailablePets = this.api.get<FilterResultsSerializationGroup<MyPetSerializationGroup>>('/pet', data).subscribe({
      next: r => {
        this.availablePets = r.data;
      }
    });
  }

  canAdd = (pet: MyPetSerializationGroup) => !this.selectedPets.some(p => p.id == pet.id);

  doAddPet(pet: MyPetSerializationGroup)
  {
    if(this.selectedPets.length >= this.step.maxPets)
      return;

    this.selectedPets.push(pet);

    this.checkRequirements();
  }

  doRemovePet(pet: MyPetSerializationGroup)
  {
    this.selectedPets = this.selectedPets.filter(p => p.id != pet.id);

    this.checkRequirements();
  }

  private checkRequirements()
  {
    this.requirementsAreMet = this.selectedPets.length >= this.step.minPets && this.selectedPets.length <= this.step.maxPets;
  }

  doChangePage(page: number)
  {
    this.page = page;
    this.loadAvailablePets();
  }

  doCancel()
  {
    this.dialogRef.close();
  }

  doSelect()
  {
    if(this.embarking) return;

    this.embarking = true;
    this.dialogRef.disableClose = true;

    const data = {
      pets: this.selectedPets.map(p => p.id)
    };

    this.api.post<{ text: string }>('/starKindred/do/' + this.step.id, data).subscribe({
      next: r => {
        this.dialogRef.close(r.data);
      },
      error: () => {
        this.embarking = false;
        this.dialogRef.disableClose = false;
      }
    });
  }

  public static open(matDialog: MatDialog, step: StepDto): MatDialogRef<AssembleTeamDialog>
  {
    return matDialog.open(AssembleTeamDialog, {
      data: {
        step: step
      }
    });
  }
}

interface StepDto
{
  id: number;
  title: string;
  type: string;
  minPets: number;
  maxPets: number;
}