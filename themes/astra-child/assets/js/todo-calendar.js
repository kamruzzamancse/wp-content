jQuery(document).ready(function($) {
    const TodoCalendar = {
        init: function() {
            this.cacheElements();
            this.bindEvents();
            this.renderCalendar(new Date());
            this.loadTodos();
        },
        
        cacheElements: function() {
            this.$container = $('.todo-calendar-container');
            this.$calendarBody = this.$container.find('.calendar-body');
            this.$formModal = this.$container.find('.todo-form-modal');
            this.$viewModal = this.$container.find('.todo-view-modal');
            this.$todoContent = this.$container.find('#todo-content');
            this.$todoDate = this.$container.find('#todo-date');
            this.$todoDetail = this.$container.find('.todo-detail-content');
        },
        
        bindEvents: function() {
            this.$container.on('click', '.calendar-prev, .calendar-next', this.handleNavClick.bind(this));
            this.$container.on('click', '.add-todo', this.showAddModal.bind(this));
            this.$container.on('click', '.save-todo', this.handleSaveTodo.bind(this));
            this.$container.on('click', '.cancel-todo, .close-todo', this.hideModals.bind(this));
            this.$container.on('click', '.todo-item', this.handleTodoClick.bind(this));
            this.$container.on('click', '.delete-todo', this.handleDeleteTodo.bind(this));
        },
        
        renderCalendar: function(date) {
            const year = date.getFullYear();
            const month = date.getMonth();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const firstDay = new Date(year, month, 1).getDay();
            
            let calendarHtml = '<tr>';
            let dayCount = 1;
            
            // Empty cells for days before the 1st
            for (let i = 0; i < firstDay; i++) {
                calendarHtml += '<td class="empty"></td>';
            }
            
            // First week
            for (let i = firstDay; i < 7; i++) {
                calendarHtml += this.getDayCell(year, month, dayCount);
                dayCount++;
            }
            
            calendarHtml += '</tr>';
            
            // Remaining weeks
            while (dayCount <= daysInMonth) {
                calendarHtml += '<tr>';
                
                for (let i = 0; i < 7 && dayCount <= daysInMonth; i++) {
                    calendarHtml += this.getDayCell(year, month, dayCount);
                    dayCount++;
                }
                
                calendarHtml += '</tr>';
            }
            
            this.$calendarBody.html(calendarHtml);
            this.updateCalendarTitle(year, month);
        },
        
        getDayCell: function(year, month, day) {
            const date = new Date(year, month, day);
            const dateString = date.toISOString().split('T')[0];
            
            return `
                <td data-date="${dateString}">
                    <div class="day-number">${day}</div>
                    <div class="todos-container"></div>
                    <button class="add-todo">+</button>
                </td>
            `;
        },
        
        updateCalendarTitle: function(year, month) {
            const monthName = new Date(year, month, 1).toLocaleString('default', { month: 'long' });
            this.$container.find('.calendar-title').text(`${monthName} ${year}`);
        },
        
        loadTodos: function() {
            $.ajax({
                url: todoCalendarVars.ajaxurl,
                type: 'POST',
                data: {
                    action: 'todo_calendar_get',
                    nonce: todoCalendarVars.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.displayTodos(response.data);
                    }
                }
            });
        },
        
        displayTodos: function(todos) {
            this.$container.find('.todos-container').empty();
            
            Object.keys(todos).sort().forEach(date => {
                const $cell = this.$container.find(`[data-date="${date}"]`);
                if ($cell.length === 0) return;
                
                const $container = $cell.find('.todos-container');
                const dateTodos = todos[date];
                
                dateTodos.forEach((todo, index) => {
                    const content = typeof todo === 'object' ? todo.display : todo;
                    const fullContent = typeof todo === 'object' ? todo.full : todo;
                    
                    $container.append(`
                        <div class="todo-item" data-date="${date}" data-index="${index}">
                            <span class="todo-content">${content}</span>
                            <button class="delete-todo">×</button>
                        </div>
                    `);
                });
            });
        },
        
        handleNavClick: function(e) {
            e.preventDefault();
            const monthChange = parseInt($(e.currentTarget).data('month'));
            const currentTitle = this.$container.find('.calendar-title').text();
            const currentDate = new Date(currentTitle);
            
            if (isNaN(currentDate)) {
                currentDate = new Date();
            }
            
            const newDate = new Date(currentDate.setMonth(currentDate.getMonth() + monthChange));
            this.renderCalendar(newDate);
            this.loadTodos();
        },
        
        showAddModal: function(e) {
            e.preventDefault();
            const date = $(e.currentTarget).closest('td').data('date');
            this.$todoDate.val(date);
            this.$todoContent.val('').focus();
            this.$formModal.show();
        },
        
        handleSaveTodo: function(e) {
            e.preventDefault();
            const content = this.$todoContent.val().trim();
            
            if (!content) {
                this.showError('Please enter a todo');
                return;
            }
            
            $.ajax({
                url: todoCalendarVars.ajaxurl,
                type: 'POST',
                data: {
                    action: 'todo_calendar_save',
                    nonce: todoCalendarVars.nonce,
                    date: this.$todoDate.val(),
                    content: content
                },
                beforeSend: () => {
                    this.$container.find('.save-todo').prop('disabled', true).text('Saving...');
                },
                success: (response) => {
                    if (response.success) {
                        this.hideModals();
                        this.loadTodos();
                    } else {
                        this.showError(response.data?.message || 'Failed to save todo');
                    }
                },
                error: (xhr) => {
                    this.showError('An error occurred. Please try again.');
                },
                complete: () => {
                    this.$container.find('.save-todo').prop('disabled', false).text('Save');
                }
            });
        },
        
        handleTodoClick: function(e) {
            e.preventDefault();
            const $todo = $(e.currentTarget);
            const date = $todo.data('date');
            const index = $todo.data('index');
            
            $.ajax({
                url: todoCalendarVars.ajaxurl,
                type: 'POST',
                data: {
                    action: 'todo_calendar_view',
                    nonce: todoCalendarVars.nonce,
                    date: date,
                    index: index
                },
                beforeSend: () => {
                    this.$viewModal.find('.todo-detail-content').html('Loading...');
                    this.$viewModal.show();
                },
                success: (response) => {
                    if (response.success) {
                        this.$viewModal.find('.todo-detail-content').html(response.data.content);
                    } else {
                        this.showError(response.data?.message || 'Failed to load todo');
                        this.$viewModal.hide();
                    }
                },
                error: () => {
                    this.showError('Failed to load todo details');
                    this.$viewModal.hide();
                }
            });
        },
        
        handleDeleteTodo: function(e) {
            e.stopPropagation();
            e.preventDefault();
            
            if (!confirm('Are you sure you want to delete this todo?')) {
                return;
            }
            
            const $button = $(e.currentTarget);
            const $todo = $button.closest('.todo-item');
            const date = $todo.data('date');
            const index = $todo.data('index');
            
            $.ajax({
                url: todoCalendarVars.ajaxurl,
                type: 'POST',
                data: {
                    action: 'todo_calendar_delete',
                    nonce: todoCalendarVars.nonce,
                    date: date,
                    index: index
                },
                beforeSend: () => {
                    $button.prop('disabled', true).text('Deleting...');
                },
                success: (response) => {
                    if (response.success) {
                        $todo.remove();
                    } else {
                        this.showError(response.data?.message || 'Failed to delete todo');
                    }
                },
                error: () => {
                    this.showError('Failed to delete todo');
                },
                complete: () => {
                    $button.prop('disabled', false).html('×');
                }
            });
        },
        
        hideModals: function() {
            this.$formModal.hide();
            this.$viewModal.hide();
        },
        
        showError: function(message) {
            let $error = this.$container.find('.todo-error');
            if ($error.length === 0) {
                $error = $('<div class="todo-error" style="color:red;margin:10px 0;"></div>');
                this.$container.prepend($error);
            }
            
            $error.text(message).show().delay(5000).fadeOut();
        }
    };
    
    TodoCalendar.init();
});