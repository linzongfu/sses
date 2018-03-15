define({ "api": [
  {
    "type": "get",
    "url": "/api/ChoiceTest",
    "title": "选择入学测试类型",
    "name": "ChoiceTest",
    "group": "EntrTest",
    "version": "1.0.0",
    "header": {
      "fields": {
        "opuser": [
          {
            "group": "opuser",
            "type": "String",
            "optional": false,
            "field": "opuser",
            "description": ""
          }
        ]
      },
      "examples": [
        {
          "title": "Header-Example:",
          "content": "{\n     opuser\n}",
          "type": "json"
        }
      ]
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "array",
            "optional": false,
            "field": "data",
            "description": ""
          }
        ]
      }
    },
    "sampleRequest": [
      {
        "url": "/api/ChoiceTest"
      }
    ],
    "filename": "app/Http/Controllers/Api/EntestController.php",
    "groupTitle": "EntrTest"
  },
  {
    "type": "get",
    "url": "/api/EnTest/:id",
    "title": "进入入学测试",
    "name": "question",
    "group": "EntrTest",
    "version": "1.0.0",
    "header": {
      "fields": {
        "opuser": [
          {
            "group": "opuser",
            "type": "String",
            "optional": false,
            "field": "opuser",
            "description": ""
          }
        ]
      },
      "examples": [
        {
          "title": "Header-Example:",
          "content": "{\n     opuser\n}",
          "type": "json"
        }
      ]
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "data",
            "description": ""
          }
        ]
      }
    },
    "sampleRequest": [
      {
        "url": "/api/EnTest/:id"
      }
    ],
    "filename": "app/Http/Controllers/Api/EntestController.php",
    "groupTitle": "EntrTest"
  },
  {
    "type": "post",
    "url": "/api/EnTest/Submit",
    "title": "提交测试结果",
    "name": "submitTest",
    "group": "EntrTest",
    "version": "1.0.0",
    "header": {
      "fields": {
        "opuser": [
          {
            "group": "opuser",
            "type": "String",
            "optional": false,
            "field": "opuser",
            "description": ""
          }
        ]
      },
      "examples": [
        {
          "title": "Header-Example:",
          "content": "{\n     opuser\n}",
          "type": "json"
        }
      ]
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "result",
            "description": "<p>结果标签id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "entest_id",
            "description": "<p>测试类型id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "useranswer",
            "description": "<p>用户回答</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "data",
            "description": ""
          }
        ]
      }
    },
    "sampleRequest": [
      {
        "url": "/api/EnTest/Submit"
      }
    ],
    "filename": "app/Http/Controllers/Api/EntestController.php",
    "groupTitle": "EntrTest"
  },
  {
    "type": "post",
    "url": "/api/Schedule/show",
    "title": "查看教师课表",
    "name": "TeacherOfSchedule",
    "group": "Schedule",
    "version": "1.0.0",
    "header": {
      "fields": {
        "opuser": [
          {
            "group": "opuser",
            "type": "String",
            "optional": false,
            "field": "opuser",
            "description": ""
          }
        ]
      },
      "examples": [
        {
          "title": "Header-Example:",
          "content": "{\n     opuser\n}",
          "type": "json"
        }
      ]
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "stage",
            "description": "<p>学年阶段</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "week",
            "description": "<p>这几周的课表</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "data",
            "description": ""
          }
        ]
      }
    },
    "sampleRequest": [
      {
        "url": "/api/Schedule/show"
      }
    ],
    "filename": "app/Http/Controllers/Api/ScheduleController.php",
    "groupTitle": "Schedule"
  },
  {
    "type": "get",
    "url": "/api/Schedule/index",
    "title": "课表选择界面",
    "name": "index",
    "group": "Schedule",
    "version": "1.0.0",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "data",
            "description": ""
          }
        ]
      }
    },
    "sampleRequest": [
      {
        "url": "/api/Schedule/index"
      }
    ],
    "filename": "app/Http/Controllers/Api/ScheduleController.php",
    "groupTitle": "Schedule"
  },
  {
    "type": "get",
    "url": "/api/teacher/showteach/:id",
    "title": "查看学生出勤",
    "name": "LookAttend",
    "group": "Teacher",
    "version": "1.0.0",
    "header": {
      "fields": {
        "opuser": [
          {
            "group": "opuser",
            "type": "String",
            "optional": false,
            "field": "opuser",
            "description": ""
          }
        ]
      },
      "examples": [
        {
          "title": "Header-Example:",
          "content": "{\n     opuser\n}",
          "type": "json"
        }
      ]
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "classid",
            "description": "<p>任课班级id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "data",
            "description": ""
          }
        ]
      }
    },
    "sampleRequest": [
      {
        "url": "/api/teacher/showteach/:id"
      }
    ],
    "filename": "app/Http/Controllers/Api/TeaController.php",
    "groupTitle": "Teacher"
  },
  {
    "type": "get",
    "url": "api/teacher/index",
    "title": "教师页面",
    "name": "index",
    "group": "Teacher",
    "version": "1.0.0",
    "header": {
      "fields": {
        "opuser": [
          {
            "group": "opuser",
            "type": "String",
            "optional": false,
            "field": "opuser",
            "description": ""
          }
        ]
      },
      "examples": [
        {
          "title": "Header-Example:",
          "content": "{\n     opuser\n}",
          "type": "json"
        }
      ]
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "data",
            "description": ""
          }
        ]
      }
    },
    "sampleRequest": [
      {
        "url": "/api/teacher/index"
      }
    ],
    "filename": "app/Http/Controllers/Api/TeaController.php",
    "groupTitle": "Teacher"
  },
  {
    "type": "get",
    "url": "/api/teacher/showteach",
    "title": "查看教学记录",
    "name": "showteach",
    "group": "Teacher",
    "version": "1.0.0",
    "header": {
      "fields": {
        "opuser": [
          {
            "group": "opuser",
            "type": "String",
            "optional": false,
            "field": "opuser",
            "description": ""
          }
        ]
      },
      "examples": [
        {
          "title": "Header-Example:",
          "content": "{\n     opuser\n}",
          "type": "json"
        }
      ]
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "classid",
            "description": "<p>任课班级id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "courseid",
            "description": "<p>课程id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "data",
            "description": ""
          }
        ]
      }
    },
    "sampleRequest": [
      {
        "url": "/api/teacher/showteach"
      }
    ],
    "filename": "app/Http/Controllers/Api/TeaController.php",
    "groupTitle": "Teacher"
  },
  {
    "type": "post",
    "url": "/api/login",
    "title": "用户登录",
    "name": "Userlogin",
    "group": "User",
    "version": "1.0.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "Noid",
            "description": "<p>学号/职工号</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "Password",
            "description": "<p>密码</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "array",
            "optional": false,
            "field": "data",
            "description": ""
          }
        ]
      }
    },
    "sampleRequest": [
      {
        "url": "/api/login"
      }
    ],
    "filename": "app/Http/Controllers/Api/UsersController.php",
    "groupTitle": "User"
  }
] });
