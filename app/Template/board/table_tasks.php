<!-- task row -->
<?php
    global $twig;
    $task_public_template = $twig->load('board/task_public.twig');
    $task_private_template = $twig->load('board/task_private.twig');
    require 'app/Core/TwigHelper.php';
?>
<tr class="board-swimlane board-swimlane-tasks-<?= $swimlane['id'] ?><?= $swimlane['task_limit'] && $swimlane['nb_tasks'] > $swimlane['task_limit'] ? ' board-task-list-limit' : '' ?>">
    <?php foreach ($swimlane['columns'] as $column): ?>
        <td class="
            board-column-<?= $column['id'] ?>
            <?= $column['task_limit'] > 0 && $column['column_nb_open_tasks'] > $column['task_limit'] ? 'board-task-list-limit' : '' ?>
            "
        >

            <!-- tasks list -->
            <div
                class="board-task-list board-column-expanded <?= $this->projectRole->isSortableColumn($column['project_id'], $column['id']) ? 'sortable-column' : '' ?>"
                data-column-id="<?= $column['id'] ?>"
                data-swimlane-id="<?= $swimlane['id'] ?>"
                data-task-limit="<?= $column['task_limit'] ?>">

                <?php foreach ($column['tasks'] as $task): ?>
                    <?php
                    $template = $task_private_template;
                    if ($not_editable) {
                        $template = $task_public_template;
                    }
                    $helper = new \Kanboard\Core\TwigHelper(
                        $this,
                        $task,
                        $project,
                        $not_editable,
                        $board_highlight_period
                    );
                    echo $template->render( array(
                        'helper' => $helper,
                        'project' => $project,
                        'task' => $task,
                        'user_perms' => array(
                                'project_access' => $this->user->hasProjectAccess('TaskModificationController', 'edit', $task['project_id']),
                                'update_task' => $this->projectRole->canUpdateTask($task),
                        ),
                    )) ?>
                <?php endforeach ?>
            </div>

            <!-- column in collapsed mode (rotated text) -->
            <div class="board-column-collapsed board-task-list sortable-column"
                data-column-id="<?= $column['id'] ?>"
                data-swimlane-id="<?= $swimlane['id'] ?>"
                data-task-limit="<?= $column['task_limit'] ?>">
                <div class="board-rotation-wrapper">
                    <div class="board-column-title board-rotation board-toggle-column-view" data-column-id="<?= $column['id'] ?>" title="<?= t('Show this column') ?>">
                        <i class="fa fa-plus-square" title="<?= $this->text->e($column['title']) ?>"></i> <?= $this->text->e($column['title']) ?>
                    </div>
                </div>
            </div>
        </td>
    <?php endforeach ?>
</tr>
