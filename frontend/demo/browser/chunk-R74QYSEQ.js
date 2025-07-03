import{a as l}from"./chunk-R744CZ6O.js";import"./chunk-6AF7U7KC.js";import{Ja as g,La as b,Z as u,_ as C,ba as h,c as f,xb as y}from"./chunk-QPBR7IA7.js";import{Eb as d,Pb as t,Qb as i,ab as r,bd as v,fb as c,lc as e,ld as E,mc as a,ob as p,ub as x,wc as S}from"./chunk-L5V4HGA2.js";import"./chunk-Q7L6LLAK.js";function T(o,s){if(o&1&&(t(0,"tr"),e(1,`
                `),t(2,"td"),e(3),i(),e(4,`
                `),t(5,"td"),e(6),i(),e(7,`
                `),t(8,"td"),e(9),i(),e(10,`
                `),t(11,"td"),e(12),i(),e(13,`
                `),t(14,"td"),e(15),i(),e(16,`
              `),i()),o&2){let n=s.$implicit,m=s.index;r(3),a(m+1),r(3),a(n.username),r(3),a(n.email),r(3),a(n.phone),r(3),a(n.user_type)}}var $=(()=>{class o{constructor(n){this.apiService=n,this.users=[]}ngOnInit(){this.apiService.getUsers().subscribe({next:n=>{this.users=n.records||[]},error:n=>{console.error("Error fetching users:",n)}})}static{this.\u0275fac=function(m){return new(m||o)(c(l))}}static{this.\u0275cmp=p({type:o,selectors:[["app-users"]],features:[S([l])],decls:46,vars:2,consts:[["xs","12"],[1,"mb-4"],["cTable","",3,"striped"],["scope","col"],[4,"ngFor","ngForOf"]],template:function(m,D){m&1&&(t(0,"c-row"),e(1,`
  
  `),t(2,"c-col",0),e(3,`
    `),t(4,"c-card",1),e(5,`
      `),t(6,"c-card-header"),e(7,`
        `),t(8,"strong"),e(9,"Users"),i(),e(10,` 
      `),i(),e(11,`
      `),t(12,"c-card-body"),e(13,`
        
          `),t(14,"table",2),e(15,`
            `),t(16,"thead"),e(17,`
            `),t(18,"tr"),e(19,`
              `),t(20,"th",3),e(21,"#"),i(),e(22,`
              `),t(23,"th",3),e(24,"Name"),i(),e(25,`
              `),t(26,"th",3),e(27,"Email"),i(),e(28,`
              `),t(29,"th",3),e(30,"Phone"),i(),e(31,`
              `),t(32,"th",3),e(33,"Type"),i(),e(34,`
        
            `),i(),e(35,`
            `),i(),e(36,`
            `),t(37,"tbody"),e(38,`
              `),x(39,T,17,5,"tr",4),e(40,`  
            `),i(),e(41,`
          `),i(),e(42,`
        
        
      `),i(),e(43,`
    `),i(),e(44,`
  `),i(),e(45,`
  
`),i()),m&2&&(r(14),d("striped",!0),r(25),d("ngForOf",D.users))},dependencies:[E,v,f,b,g,u,h,C,y],encapsulation:2})}}return o})();export{$ as UsersComponent};
