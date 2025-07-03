import{a as x}from"./chunk-HEZBKCVY.js";import{Ja as k,La as w,Z as T,_ as P,ba as N,c as D,m as F,xb as M}from"./chunk-QPBR7IA7.js";import{Ac as u,Bc as S,Cc as E,Eb as o,Pb as e,Qb as n,Rb as f,ab as r,ad as _,bd as h,cd as I,fb as b,hd as j,jd as J,lc as t,ld as L,mc as l,nc as c,ob as g,oc as v,ub as p,wc as y,yc as C}from"./chunk-L5V4HGA2.js";import"./chunk-Q7L6LLAK.js";var O=(a,m,i)=>({"bg-warning":a,"bg-info":m,"bg-success":i}),B=a=>["/base/addjob",a];function H(a,m){a&1&&(e(0,"tr"),t(1,`
                `),e(2,"td",6),t(3,`
                    `),e(4,"div",7),t(5,`
                        `),e(6,"span",8),t(7,"Loading..."),n(),t(8,`
                    `),n(),t(9,`
                `),n(),t(10,`
              `),n())}function R(a,m){a&1&&(e(0,"tr"),t(1,`
                  `),e(2,"td",6),t(3,"No jobs found"),n(),t(4,`
              `),n())}function A(a,m){if(a&1&&(e(0,"tr"),t(1,`
                  `),e(2,"td"),t(3),n(),t(4,`
                  `),e(5,"td"),t(6),n(),t(7,`
                  `),e(8,"td"),t(9),n(),t(10,`
                  `),e(11,"td"),t(12),n(),t(13,`
                  `),e(14,"td"),t(15),S(16,"date"),n(),t(17,`
                  `),e(18,"td"),t(19),S(20,"currency"),n(),t(21,`
                  `),e(22,"td"),t(23,`
                      `),e(24,"span",9),t(25),n(),t(26,`
                  `),n(),t(27,`
                  `),e(28,"td"),t(29,`
                    `),e(30,"span",9),t(31),n(),t(32,`
                `),n(),t(33,`
                  `),e(34,"td"),t(35,`
                    `),e(36,"a",10),t(37,`
                      `),f(38,"i",11),t(39,` Edit
                    `),n(),t(40,`
                  `),n(),t(41,`
              `),n()),a&2){let i=m.$implicit;r(3),l(i.id),r(3),v("",i.full_name," (ID: ",i.user_id,")"),r(3),l(i.item_type),r(3),l(i.problem_description),r(3),l(E(16,12,i.estimated_delivery)),r(4),l(E(20,14,i.estimated_price)),r(5),o("ngClass",u(16,O,i.status==="pending",i.status==="in_progress",i.status==="completed")),r(),c(`
                          `,i.status,`
                      `),r(5),o("ngClass",u(20,O,i.payment_status==="pending",i.payment_status==="in_progress",i.payment_status==="paid")),r(),c(`
                        `,i.payment_status,`
                    `),r(5),o("routerLink",C(24,B,i.id))}}var X=(()=>{class a{constructor(i){this.jobService=i,this.jobs=[],this.isLoading=!0}ngOnInit(){this.loadJobs()}loadJobs(){this.jobService.getJobs().subscribe({next:i=>{console.log(i),this.jobs=i.data||i,this.isLoading=!1},error:i=>{console.error("Error loading jobs:",i),this.isLoading=!1}})}static{this.\u0275fac=function(d){return new(d||a)(b(x))}}static{this.\u0275cmp=g({type:a,selectors:[["app-jobs"]],features:[y([x])],decls:62,vars:4,consts:[["xs","12"],[1,"mb-4"],["cTable","",3,"striped"],["scope","col"],[4,"ngIf"],[4,"ngFor","ngForOf"],["colspan","7",1,"text-center"],["role","status",1,"spinner-border"],[1,"visually-hidden"],[1,"badge",3,"ngClass"],["cNavLink","",3,"routerLink"],[1,"cil-energy"]],template:function(d,s){d&1&&(e(0,"c-row"),t(1,`
  
  `),e(2,"c-col",0),t(3,`
    `),e(4,"c-card",1),t(5,`
      `),e(6,"c-card-header"),t(7,`
        `),e(8,"strong"),t(9,"Jobs"),n(),t(10,` 
      `),n(),t(11,`
      `),e(12,"c-card-body"),t(13,`
        
          `),e(14,"table",2),t(15,`
            `),e(16,"thead"),t(17,`
            `),e(18,"tr"),t(19,`
              `),e(20,"th",3),t(21,"ID"),n(),t(22,`
              `),e(23,"th",3),t(24,"Customer"),n(),t(25,`
              `),e(26,"th",3),t(27,"Device Type"),n(),t(28,`
              `),e(29,"th",3),t(30,"Problem"),n(),t(31,`
              `),e(32,"th",3),t(33,"Est. Delivery"),n(),t(34,`
              `),e(35,"th",3),t(36,"Est. Price"),n(),t(37,`
              `),e(38,"th",3),t(39,"Status"),n(),t(40,`
              `),e(41,"th",3),t(42,"Payment"),n(),t(43,`
              `),e(44,"th",3),t(45,"Action"),n(),t(46,`
            `),n(),t(47,`
            `),n(),t(48,`
            `),e(49,"tbody"),t(50,`
              `),p(51,H,11,0,"tr",4),t(52,`
              `),p(53,R,5,0,"tr",4),t(54,`
              `),p(55,A,42,26,"tr",5),t(56,`
            `),n(),t(57,`
          `),n(),t(58,`
        
        
      `),n(),t(59,`
    `),n(),t(60,`
  `),n(),t(61,`
  
`),n()),d&2&&(r(14),o("striped",!0),r(37),o("ngIf",s.isLoading),r(2),o("ngIf",s.jobs.length===0&&!s.isLoading),r(2),o("ngForOf",s.jobs))},dependencies:[L,_,h,I,J,j,D,w,k,T,N,P,M,F],encapsulation:2})}}return a})();export{X as JobsComponent};
