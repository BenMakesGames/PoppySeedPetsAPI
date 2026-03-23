import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import {PetShelterRoutingModule} from "./pet-shelter-routing.module";
import {PetShelterComponent} from "./page/pet-shelter/pet-shelter.component";
import {PickUpComponent} from "./page/pet-shelter/pick-up/pick-up.component";
import {MarkdownModule} from "ngx-markdown";
import {FormsModule} from "@angular/forms";
import {InteractWithPetShelterPetDialog} from "./dialog/interact-with-pet-shelter-pet/interact-with-pet-shelter-pet.dialog";
import { NpcDialogComponent } from "../shared/component/npc-dialog/npc-dialog.component";
import { LoadingThrobberComponent } from "../shared/component/loading-throbber/loading-throbber.component";
import { ObserveOnScreenDirective } from "../shared/directive/observe-on-screen.directive";
import { PaginatorComponent } from "../shared/component/paginator/paginator.component";
import { PetMiniComponent } from "../shared/component/pet-mini/pet-mini.component";
import { PetSearchComponent } from "../shared/component/pet-search/pet-search.component";
import { MoneysComponent } from "../shared/component/moneys/moneys.component";
import { PetAppearanceComponent } from "../shared/component/pet-appearance/pet-appearance.component";
import { AttributesTableComponent } from "../shared/component/attributes-table/attributes-table.component";
import { PetMeritsComponent } from "../shared/component/pet-merits/pet-merits.component";
import { PetNotesComponent } from "../shared/component/pet-notes/pet-notes.component";
import { PetActivityLogTableComponent } from "../shared/component/pet-activity-log-table/pet-activity-log-table.component";
import { PetFriendsComponent } from "../shared/component/pet-friends/pet-friends.component";
import { SkillBonusesComponent } from "../shared/component/skill-bonuses/skill-bonuses.component";
import { SkillsTableComponent } from "../shared/component/skills-table/skills-table.component";
import { HelpLinkComponent } from "../shared/component/help-link/help-link.component";
import { ReallyReleasePetDialog } from "./dialog/really-release-pet/really-release-pet.dialog";
import {PetBadgeTableComponent} from "../shared/pet-badge-table/pet-badge-table.component";
import { PetLogsLinksComponent } from "../shared/component/pet-logs-links/pet-logs-links.component";
import {
    PetSkillsAndAttributesPanelComponent
} from "../shared/component/pet-skills-and-attributes-panel/pet-skills-and-attributes-panel.component";
import { RenameRowComponent } from "../pet-management/components/rename-row/rename-row.component";
import { VolagamyRowComponent } from "../pet-management/components/volagamy-row/volagamy-row.component";
import { PetStatusEffectsComponent } from "../shared/component/pet-status-effects/pet-status-effects.component";
import { PetStatusEffectsTabComponent } from "../pet-management/components/pet-status-effects-tab/pet-status-effects-tab.component";

@NgModule({
  declarations: [
    PetShelterComponent,
    PickUpComponent,
    InteractWithPetShelterPetDialog,
    ReallyReleasePetDialog
  ],
  imports: [
    CommonModule,
    PetShelterRoutingModule,
    MarkdownModule,
    FormsModule,
    NpcDialogComponent,
    LoadingThrobberComponent,
    ObserveOnScreenDirective,
    PaginatorComponent,
    PetMiniComponent,
    PetSearchComponent,
    MoneysComponent,
    PetAppearanceComponent,
    AttributesTableComponent,
    PetMeritsComponent,
    PetNotesComponent,
    PetActivityLogTableComponent,
    PetFriendsComponent,
    SkillBonusesComponent,
    SkillsTableComponent,
    HelpLinkComponent,
    PetBadgeTableComponent,
    PetLogsLinksComponent,
    PetSkillsAndAttributesPanelComponent,
    RenameRowComponent,
    VolagamyRowComponent,
    PetStatusEffectsComponent,
    PetStatusEffectsTabComponent
  ]
})
export class PetShelterModule { }
