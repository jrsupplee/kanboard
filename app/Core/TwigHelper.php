<?php


namespace Kanboard\Core;


/**
 * TwigHelper
 *
 * @property \Kanboard\Core\Template  $template
 * @property                          $task
 * @property                          $project
 * @property boolean                  $not_editable
 * @property                          $board_highlight_period
 */
class TwigHelper
{
    public function __construct($template, $task, $project, $not_editable, $board_highlight_period)
    {
        $this->template = $template;
        $this->task = $task;
        $this->project = $project;
        $this->not_editable = $not_editable;
        $this->board_highlight_period = $board_highlight_period;
    }

    public function task_not_editable() {
        return $this->not_editable;
    }

    public function board_is_collapsed() {
        return $this->template->board->isCollapsed($this->task['project_id']);
    }

    protected function hook_render($hook)
    {
        return $this->template->hook->render($hook, array('task' => $this->task));
    }

    protected function task_hook($position)
    {
        $visibility = $this->not_editable ? 'public' : 'private';
        return $this->hook_render("template:board:$visibility:task:$position-title");
    }

    public function task_hook_before()
    {
        return $this->task_hook('before');
    }

    public function task_hook_after()
    {
        return $this->task_hook('after');
    }

    public function hook_render_task_icons() {
        return $this->hook_render('template:board:task:icons');
    }

    public function hook_render_task_footer() {
        return $this->hook_render('template:board:task:icons');
    }

    public function title() {
        if ($this->not_editable) {
            return $this->template->url->link(
                $this->template->text->e($this->task['title']),
                'TaskViewController',
                'readonly',
                array('task_id' => $this->task['id'], 'token' => $this->project['token'])
            );
        } elseif ($this->board_is_collapsed()) {
            return $this->template->url->link(
                $this->template->text->e($this->task['title']),
                'TaskViewController',
                'show',
                array('task_id' => $this->task['id'], 'project_id' => $this->task['project_id']),
                false,
                '',
                $this->template->text->e($this->task['title'])
            );
        }

        return $this->template->url->link(
            $this->template->text->e($this->task['title']),
            'TaskViewController',
            'show',
            array('task_id' => $this->task['id'], 'project_id' => $this->task['project_id'])
        );
    }

    public function dropdown() {
        return $this->template->render('task/dropdown', array('task' => $this->task, 'redirect' => 'board'));
    }

    public function edit_task() {
        return $this->template->modal->large(
            'edit',
            '',
            'TaskModificationController',
            'edit', array('task_id' => $this->task['id'], 'project_id' => $this->task['project_id'])
        );
    }

    public function recently_modified() {
        return $this->task['date_modification'] > time() - $this->board_highlight_period;
    }

    public function view_href() {
        if ($this->not_editable) {
            return $this->template->url->href(
                'TaskViewController',
                'readonly',
                array(
                    'task_id' => $this->task['id'],
                    'token' => $this->project['token']
                )
            );
        } else {
            return $this->template->url->href(
                'TaskViewController',
                'show',
                array(
                    'task_id' => $this->task['id'],
                    'project_id' => $this->task['project_id']
                )
            );
        }
    }

    public function assignee_name() {
        return $this->task['assignee_name'] ? $this->task['assignee_name'] : $this->task['assignee_username'];
    }

    public function assignee_initials() {
        return $this->template->user->getInitials($this->assignee_name());
    }

    public function assignee_avatar() {
        return $this->template->avatar->small(
            $this->task['owner_id'],
            $this->task['assignee_username'],
            $this->task['assignee_name'],
            $this->task['assignee_email'],
            $this->task['assignee_avatar_path'],
            'avatar-inline'
        );
    }

    public function task_footer() {
        return $this->template->render(
            'board/task_footer',
            array(
                'task' => $this->task,
                'not_editable' => $this->not_editable,
                'project' => $this->project,
            )
        );
    }

    public function task_overdue() {
        return time() > $this->task['date_due'];
    }

    public function task_due_today() {
        return date('Y-m-d') == date('Y-m-d', $this->task['date_due']);
    }

    public function task_due_formatted() {
        if (date('Hi', $this->task['date_due']) === '0000' ) {
             $this->template->dt->date($this->task['date_due']);
        } else {
            $this->template->dt->datetime($this->task['date_due']);
        }
    }

    public function task_date_created() {
        return $this->template->dt->age($this->task['date_creation']);
    }

    public function task_date_moved() {
        return $this->template->dt->age($this->task['date_moved']);
    }

    public function task_description_icon() {
        return $this->template->app->tooltipMarkdown($this->task['category_description']);
    }

    public function task_category_href() {
        return $this->template->url->href(
            'TaskModificationController',
            'edit',
            array('task_id' => $this->task['id'], 'project_id' => $this->task['project_id']),
            false,
            'js-modal-large' . (! empty($task['category_description']) ? ' tooltip' : ''),
            t('Change category')
        );
    }

    protected function task_tooltip_href($action) {
        return $this->template->url->href(
            'BoardTooltipController',
            $action,
            array('task_id' => $this->task['id'], 'project_id' => $this->task['project_id'])
        );
    }

    public function task_description_href() {
        return $this->task_tooltip_href('description');
    }

    public function task_recurrence_href() {
        return $this->task_tooltip_href('recurrence');
    }

    public function task_nb_links_href() {
        return $this->task_tooltip_href('tasklinks');
    }

    public function task_nb_external_links_href() {
        return $this->task_tooltip_href('externallinks');
    }

    public function task_nb_subtasks_href() {
        return $this->task_tooltip_href('subtasks');
    }

    public function task_nb_files_href() {
        return $this->task_tooltip_href('attachments');
    }

    public function RECURRING_STATUS_PENDING() {
        return \Kanboard\Model\TaskModel::RECURRING_STATUS_PENDING;
    }

    public function RECURRING_STATUS_PROCESSED() {
        return \Kanboard\Model\TaskModel::RECURRING_STATUS_PROCESSED;
    }

    public function task_render_priority() {
        return $this->template->task->renderPriority($this->task['priority']);
    }

    public function task_comments_modal() {
        $this->template->modal->medium(
            'comments-o',
            $this->task['nb_comments'],
            'CommentListController',
            'show',
            array('task_id' => $this->task['id'], 'project_id' => $this->task['project_id']),
            $this->task['nb_comments'] == 1
                ? t('%d comment', $this->task['nb_comments'])
                : t('%d comments', $this->task['nb_comments'])
        );
    }
}
