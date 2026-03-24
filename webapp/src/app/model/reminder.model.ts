export interface ReminderModel
{
  id: number;
  text: string;
  nextReminder: string;
  reminderInterval: number|null;
}